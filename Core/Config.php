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

            if($this->isBisWebPreferredDomainPreferredDomainSsl() && $blSsl === false) {
                // Redirection from http to https
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
        $aParseUrl = $this->_parseBisWebPreferredDomainUrl($sShopURL);
        $iCountPoint = substr_count($sShopURL, '.');
        // with and without Third-Level e.g. www. or dev.
        if($iCountPoint == 1 OR $iCountPoint == 2) {
            $aOptions['https://www.example.com']    = 'https://'.$aParseUrl['third_level'].$aParseUrl['second_level'].$aParseUrl['top_level'];
            $aOptions['https://example.com']        = 'https://'.$aParseUrl['second_level'].$aParseUrl['top_level'];
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

        // Protocol
        if(strpos($url, 'https://') !== false) {
            $sProtocol = 'https://';
        } else {
            $sProtocol = 'http://';
        }

        // Third Level Domain
        $iCountPoint = substr_count($url, '.');
        $aUrl = explode('.', str_replace($sProtocol, '', $url));
        if($iCountPoint == 1) {
            $sThirdLevel = '';
        } elseif($iCountPoint == 2) {
            $sThirdLevel = $aUrl[0].'.';
        } else {
            $sThirdLevel = '';
        }

        // Second Level Domain
        $sSecondLevel = $aUrl[1];

        // Top Level Domain
        $sTopLevel = '.'.$aUrl[2];

        // URL Parsing
        $aParseUrl['protocol']      = $sProtocol; // http://
        $aParseUrl['third_level']   = $sThirdLevel; // www.
        $aParseUrl['second_level']  = $sSecondLevel; // example
        $aParseUrl['top_level']     = $sTopLevel; // .com/ oder .com/shop/

        return $aParseUrl;
    }

}
