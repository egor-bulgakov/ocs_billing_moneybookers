<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * /install.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers
 * @since 1.2.6
 */
$billingService = BOL_BillingService::getInstance();

$gateway = new BOL_BillingGateway();
$gateway->gatewayKey = 'ocsbillingmoneybookers';
$gateway->adapterClassName = 'OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter';
$gateway->active = 0;
$gateway->mobile = 0;
$gateway->recurring = 1;
$gateway->currencies = 'EUR,USD,GBP,HKD,SGD,JPY,CAD,AUD,CHF,DKK,SEK,NOK,ILS,MYR,NZD,TRY,AED,MAD,QAR,SAR,TWD,THB,CZK,HUF,SKK,EEK,BGN,PLN,ISK,INR,LVL,KRW,ZAR,RON,HRK,LTL,JOD,OMR,RSD,TND';

$billingService->addGateway($gateway);

$billingService->addConfig('ocsbillingmoneybookers', 'merchantId', '');
$billingService->addConfig('ocsbillingmoneybookers', 'merchantEmail', '');
$billingService->addConfig('ocsbillingmoneybookers', 'secret', '');
$billingService->addConfig('ocsbillingmoneybookers', 'recipientDescription', '');
$billingService->addConfig('ocsbillingmoneybookers', 'sandboxMode', '0');
$billingService->addConfig('ocsbillingmoneybookers', 'language', 'EN');

OW::getPluginManager()->addPluginSettingsRouteName('ocsbillingmoneybookers', 'ocsbillingmoneybookers.admin');

$path = OW::getPluginManager()->getPlugin('ocsbillingmoneybookers')->getRootDir() . 'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'ocsbillingmoneybookers');