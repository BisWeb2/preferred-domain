<?php

/**
 * @license MIT License
 * @copyright Tim Bischoff - Softwareentwickler
 * @link https://bisweb.me/
 * @author Tim Bischoff - Softwareentwickler <tim.bischoff@bisweb.me>
 */

namespace BisWeb\PreferredDomain\Core;

use OxidEsales\EshopCommunity\Core\Registry;

class Config extends Config_parent
{

    protected $sBisWebPreferredDomainShopURL = null;

    protected function _loadVarsFromFile()
    {
        // config variables from config.inc.php takes priority over the ones loaded from db
        parent::_loadVarsFromFile();

        // adding trailing slashes
        $fileUtils = Registry::getUtilsFile();
        $this->sBisWebPreferredDomainShopURL = $fileUtils->normalizeDir($this->sBisWebPreferredDomainShopURL);
    }

    public function isSsl()
    {
        $blSsl = parent::isSsl();

        $this->handlingBisWebPreferredDomainRedirection();

        return $blSsl;
    }

    public function getShopUrl($lang = null, $admin = null)
    {
        $url = parent::getShopUrl($lang, $admin);

        $this->handlingBisWebPreferredDomainRedirection();

        return $url;
    }

    public function getSslShopUrl($lang = null)
    {
        $url = parent::getSslShopUrl($lang);

        $this->handlingBisWebPreferredDomainRedirection();

        return $url;
    }

    public function handlingBisWebPreferredDomainRedirection()
    {
        // only frontend redirection
        $admin = $this->isAdmin();
        if($admin === false) {
            $myUtilsUrl = Registry::getUtilsUrl();
            $sCurrentUrl = $myUtilsUrl->getCurrentUrl();

            if(
                $this->isBisWebPreferredDomainPreferredDomainSsl() &&
                $this->ifBisWebPreferredDomainCheckRedirectionNeeded($sCurrentUrl)
            ) {
                $this->setIsSsl(true);
                $sRedirectUrl = $this->getBisWebPreferredDomainRedirectUrl($sCurrentUrl);
                $this->redirectBisWebPreferredDomain($sRedirectUrl);
            }
        }
    }

    public function getBisWebPreferredDomainOptions()
    {
        $aOptions = [];

        $oRegistry = Registry::getConfig();
        $sShopURL = $oRegistry->getConfigParam('sShopURL');
        $blSsl = $oRegistry->isSsl();

        if($blSsl) {
            $blHttpsOnly = $oRegistry->isHttpsOnly();
            if($blHttpsOnly) {
                // Check is "normal" Shop URL
                $iCountPoint = substr_count($sShopURL, '.');
                if($iCountPoint == 1 OR $iCountPoint == 2) {
                    $aParseUrl = $this->_parseBisWebPreferredDomainUrl($sShopURL);
                    if($aParseUrl['third_level'] === '') {
                        $aOptions['https://example.com']        = 'https://'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
                    } elseif($aParseUrl['third_level'] === 'www') {
                        $aOptions['https://www.example.com']    = 'https://'.$aParseUrl['third_level'].'.'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
                    } else {
                        $aOptions['https://www.example.com']    = 'https://'.$aParseUrl['third_level'].'.'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
                    }
                } else {
                    // error
                    $aOptions['unexpected_error'] = $sShopURL;
                }
            } else {
                // error
                $aOptions['https_only_error'] = $sShopURL;
            }
        } else {
            // error
            $aOptions['no_https_error'] = $sShopURL;
        }

        return $aOptions;
    }

    public function redirectBisWebPreferredDomain($sRedirectUrl = false)
    {
        if($sRedirectUrl === false) {
            $sRedirectUrl = $this->getBisWebPreferredDomainPreferredDomain();
        }
        Registry::getUtils()->redirect($sRedirectUrl, false, 301);
    }

    public function getBisWebPreferredDomainPreferredDomain()
    {
        $preferredDomain = Registry::getConfig()->getConfigParam('sBisWebPreferredDomainShopURL');
        return $preferredDomain;
    }

    public function isBisWebPreferredDomainPreferredDomainSsl()
    {
        $blPreferredDomainSsl = false;

        $preferredDomain = $this->getBisWebPreferredDomainPreferredDomain();
        if(strpos($preferredDomain, 'https://') !== false) {
            $blPreferredDomainSsl = true;
        }

        return $blPreferredDomainSsl;
    }

    public function ifBisWebPreferredDomainCheckRedirectionNeeded($url)
    {
        $blRedirectionNeeded = false;

        $sBisWebPreferredDomainShopURL = $this->getBisWebPreferredDomainPreferredDomain();
        if(
            strpos($sBisWebPreferredDomainShopURL, 'example.com') === false &&
            strpos($url, $sBisWebPreferredDomainShopURL) === false
        ) {
            $blRedirectionNeeded = true;
        }

        return $blRedirectionNeeded;
    }

    public function getBisWebPreferredDomainRedirectUrl($sCurrentUrl)
    {
        // e.g. https://example.com/category/ must be https://www.example.com/category/

        $aParseUrl = $this->_parseBisWebPreferredDomainUrl($sCurrentUrl);
        $sUrlEnding = $aParseUrl['second_level'].'.'.$aParseUrl['top_level'].'/'.$aParseUrl['path'];
        if($aParseUrl['third_level'] == '') {
            $sHttpWww = 'http://www.'.$sUrlEnding;
            $sHttpsWww = 'https://www.'.$sUrlEnding;
        } else {
            $sHttpWww = 'http://'.$aParseUrl['third_level'].'.'.$sUrlEnding;
            $sHttpsWww = 'https://'.$aParseUrl['third_level'].'.'.$sUrlEnding;
        }
        $sHttp = 'http://'.$sUrlEnding;
        $sHttps = 'https://'.$sUrlEnding;
        $aSearchesOtherCases = [$sHttpWww, $sHttp, $sHttpsWww, $sHttps];

        $sBisWebPreferredDomainShopURL = $this->getBisWebPreferredDomainPreferredDomain();
        if($aParseUrl['path'] == '') {
            $sReplaceUrl = $sBisWebPreferredDomainShopURL;
        } else {
            $sReplaceUrl = $sBisWebPreferredDomainShopURL.$aParseUrl['path'];
        }
        $aReplacesRedirection = [$sReplaceUrl, $sReplaceUrl, $sReplaceUrl, $sReplaceUrl];

        $sRedirection = str_replace($aSearchesOtherCases, $aReplacesRedirection, $sCurrentUrl);

        return $sRedirection;
    }

    protected function _parseBisWebPreferredDomainUrl($url)
    {
        $aParseUrl = [];

        // Cases
        // $url = 'https://www.example.com/';
        // $url = 'https://example.com/';
        // $url = 'https://www.example.com/shop/';
        // $url = 'https://example.com/shop/';
        $aUrl = parse_url($url);

        // Protocol
        $sProtocol = $aUrl['scheme'];

        // Level Domain Parts
        $iCountPoint = substr_count($aUrl['host'], '.');
        $aLevelDomainParts = explode('.', $aUrl['host']);
        if($iCountPoint == 1) {
            $sThirdLevel    = '';
            $sSecondLevel   = $aLevelDomainParts[0];
            $sTopLevel      = $aLevelDomainParts[1];
        } elseif($iCountPoint == 2) {
            $sThirdLevel    = $aLevelDomainParts[0];
            $sSecondLevel   = $aLevelDomainParts[1];
            $sTopLevel      = $aLevelDomainParts[2];
        } else {
            // Fallback
            $sThirdLevel    = '';
            $sSecondLevel   = '';
            $sTopLevel      = '';
        }

        // Extend Top Level Domain
        if(isset($aUrl['path'])) {
            $sPath = substr($aUrl['path'], 1); // removing leading slash
        } else {
            $sPath = '';
        }

        // URL Parsing
        $aParseUrl['protocol']      = $sProtocol; // https://
        $aParseUrl['third_level']   = $sThirdLevel; // empty, www or dev
        $aParseUrl['second_level']  = $sSecondLevel; // example
        $aParseUrl['top_level']     = $sTopLevel; // com
        $aParseUrl['path']          = $sPath; // empty or shop/

        return $aParseUrl;
    }

}
