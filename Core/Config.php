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
        //config variables from config.inc.php takes priority over the ones loaded from db
        parent::_loadVarsFromFile();

        //adding trailing slashes
        $fileUtils = Registry::getUtilsFile();
        $this->sBisWebPreferredDomainShopURL = $fileUtils->normalizeDir($this->sBisWebPreferredDomainShopURL);
    }

    public function isSsl()
    {
        $blSsl = parent::isSsl();

        // only frontend redirection
        $admin = $this->isAdmin();
        if($admin === false) {
            $myUtilsUrl = Registry::getUtilsUrl();
            $sCurrentUrl = $myUtilsUrl->getCurrentUrl();

            if($this->isBisWebPreferredDomainPreferredDomainSsl()) {
                if($this->ifBisWebPreferredDomainCheckRedirectionNeeded($sCurrentUrl)) {
                    $this->setIsSsl(true);
                    $this->redirectBisWebPreferredDomain();
                }
            }
        }

        return $blSsl;
    }

    public function getShopUrl($lang = null, $admin = null)
    {
        $url = parent::getShopUrl($lang, $admin);

        // only frontend redirection
        $admin = isset($admin) ? $admin : $this->isAdmin();
        if($admin === false && $this->ifBisWebPreferredDomainCheckRedirectionNeeded($url)) {
            $this->redirectBisWebPreferredDomain();
        }

        return $url;
    }

    public function getSslShopUrl($lang = null)
    {
        $url = parent::getSslShopUrl($lang);

        // only frontend redirection
        $admin = $this->isAdmin();
        if($admin === false && $this->ifBisWebPreferredDomainCheckRedirectionNeeded($url)) {
            $this->redirectBisWebPreferredDomain();
        }

        return $url;
    }

    public function getBisWebPreferredDomainOptions()
    {
        $aOptions = [];

        $sShopURL = Registry::getConfig()->getConfigParam("sShopURL");

        // Check is "normal" Shop URL
        $iCountPoint = substr_count($sShopURL, '.');
        if($iCountPoint == 1 OR $iCountPoint == 2) {
            $aParseUrl = $this->_parseBisWebPreferredDomainUrl($sShopURL);
            if($aParseUrl['third_level'] === '') {
                $aOptions['https://www.example.com']    = 'https://www.'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
                $aOptions['https://example.com']        = 'https://'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
            } elseif($aParseUrl['third_level'] === 'www') {
                $aOptions['https://www.example.com']    = 'https://'.$aParseUrl['third_level'].'.'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
                $aOptions['https://example.com']        = 'https://'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
            } else {
                $aOptions['https://www.example.com']    = 'https://'.$aParseUrl['third_level'].'.'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
                $aOptions['https://example.com']        = 'https://'.$aParseUrl['third_level'].'.'.$aParseUrl['second_level'].'.'.$aParseUrl['top_level'];
            }
        } else {
            // error
            $aOptions['error'] = $sShopURL;
        }

        return $aOptions;
    }

    public function redirectBisWebPreferredDomain()
    {
        $sRedirectUrl = $this->getBisWebPreferredDomainPreferredDomain();
        Registry::getUtils()->redirect($sRedirectUrl, false, 301);
    }

    public function getBisWebPreferredDomainPreferredDomain()
    {
        $preferredDomain = Registry::getConfig()->getConfigParam("sBisWebPreferredDomainShopURL");
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
            $url !== $sBisWebPreferredDomainShopURL
        ) {
            $blRedirectionNeeded = true;
        }

        return $blRedirectionNeeded;
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
            $sTopLevel = $sTopLevel.$aUrl['path'];
        }

        // URL Parsing
        $aParseUrl['protocol']      = $sProtocol; // https://
        $aParseUrl['third_level']   = $sThirdLevel; // www or dev
        $aParseUrl['second_level']  = $sSecondLevel; // example
        $aParseUrl['top_level']     = $sTopLevel; // com/ or com/shop/

        return $aParseUrl;
    }

}
