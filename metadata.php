<?php

/**
 * @license MIT License
 * @copyright Tim Bischoff - Softwareentwickler
 * @link https://bisweb.me/
 * @author Tim Bischoff - Softwareentwickler <tim.bischoff@bisweb.me>
 */

use OxidEsales\EshopCommunity\Core\Registry;

$sMetadataVersion = '2.1';

$aModule = [
    'id' => 'bisweb_preferreddomain',
    'title' => 'BisWeb - Bevorzugte Domain',
    'description' => 'Modul veranlasst 301 HTTP-Status Weiterleitung, wenn nicht bevorzugte Domain aufgerufen wird',
    'version' => '1.0.0',
    'author' => 'Tim Bischoff - Softwareentwickler',
    'url' => 'https://bisweb.me/module/seo/bevorzugte-domain.html',
    'email' => 'tim.bischoff@bisweb.me',
    'extend' => [
        // Core
        \OxidEsales\Eshop\Core\Config::class => \BisWeb\PreferredDomain\Core\Config::class,
    ],
    'events' => [
        'onActivate'    => 'BisWeb\PreferredDomain\Core\Events::onActivate',
    ],
    'settings' => [
        ['group' => 'main', 'name' => 'sBisWebPreferredDomainShopURL', 'type' => 'select', 'value' => 'https://example.com', 'constraints' => Registry::getConfig()->getConfigParam('sSSLShopURL').'|'.str_replace('www', '', Registry::getConfig()->getConfigParam('sSSLShopURL')), 'position' => 0],
    ],
];
