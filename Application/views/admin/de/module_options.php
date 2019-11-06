<?php

/**
 * @license MIT License
 * @copyright Tim Bischoff - Softwareentwickler
 * @link https://bisweb.me/
 * @author Tim Bischoff - Softwareentwickler <tim.bischoff@bisweb.me>
 */

use OxidEsales\EshopCommunity\Core\Registry;

$sLangName = 'Deutsch';

$aLang = [
    'charset' => 'UTF-8',
    'SHOP_MODULE_GROUP_main' => 'Einstellungen',
    'SHOP_MODULE_sBisWebPreferredDomainShopURL' => 'Bevorzugte Domain ausw&auml;hlen',
    'SHOP_MODULE_sBisWebPreferredDomainShopURL_https://www.example.com' => 'https://www.example.com',
    'SHOP_MODULE_sBisWebPreferredDomainShopURL_https://example.com' => 'https://example.com',
    'SHOP_MODULE_sBisWebPreferredDomainShopURL_error' => 'Unerwarteter Fehler, mehr als zwei Punkte in "sShopURL" Variable',
    'HELP_SHOP_MODULE_sBisWebPreferredDomainShopURL' => 'Alle anderen 3 Domainvariationen werden auf bevorzugte Domain weitergeleitet',
];

$oConfig = Registry::getConfig();
if(method_exists($oConfig, 'getBisWebPreferredDomainOptions')) {
    $aPreferredDomainOptions = $oConfig->getBisWebPreferredDomainOptions();
    foreach($aPreferredDomainOptions as $suffix => $url) {
        if($suffix != 'error') {
            $aLang['SHOP_MODULE_sBisWebPreferredDomainShopURL_'.$url] = $url;
        }
    }
}
