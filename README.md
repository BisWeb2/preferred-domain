# Preferred Domain Setting Module for OXID eShop Community Edition

This module ensures that only the preferred domain callable. The other domain variants
are forwarded.

## Feature

Set your preferred Domain in config.inc.php and save your preferred Domain in Module Settings.

## Recommended

I recommended to use SSL Protocol. In consequence this Module only provided SSL Protocol.

## Possible Domains Variants

- http://www.example.com 
- https://www.example.com
- http://example.com
- https://example.com

This Module set your preferred Domain and for the other three Domains set a permanently 
Redirection with 301 HTTP-Status. 

## Requirements

* OXID eShop Community Edition Version 6 or higher
* Access via Console possible
* Composer is installed on your Server

## Preparation Configuration

Set your preferred Domain in config.inc.php for both config variables. 

Hint remember to change the file permissions to edit the file.

```bash
chmod 666 config.inc.php
```

```php
$this->sShopURL     = 'https://www.example.com'; // eShop base url, required
$this->sSSLShopURL  = 'https://www.example.com'; // eShop SSL url, optional
```

```bash
chmod 444 config.inc.php
```

Clear your tmp Directory.

## Module Installation via Composer

1. Login via SSH and change to Shop Root Directory 
2. Register the Module via Composer
3. Login into Shop Admin Backend
4. Activate Preferred Domain Module
5. See your preferred Domain in the Module Setting Tab
6. Select the shown preferred Domain and use Save Button (required)

## Composer Install Command

```bash
composer require --no-update bisweb/preferreddomain:v1.0.0
composer update --no-dev
```