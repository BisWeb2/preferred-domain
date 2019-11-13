<?php

/**
 * @license MIT License
 * @copyright Tim Bischoff - Softwareentwickler
 * @link https://bisweb.me/
 * @author Tim Bischoff - Softwareentwickler <tim.bischoff@bisweb.me>
 */

namespace BisWeb\PreferredDomain\Core;

use OxidEsales\EshopCommunity\Core\Registry;

class Events
{

    public static function onActivate()
    {
        $oRegistry = Registry::getConfig();
        $blHttpsOnly = $oRegistry->isHttpsOnly();
        $preferredDomain = $oRegistry->getConfigParam('sSSLShopURL');
        if($blHttpsOnly && $preferredDomain !== null) {
            $oRegistry->setConfigParam('sBisWebPreferredDomainShopURL', $preferredDomain);
        }
    }

}
