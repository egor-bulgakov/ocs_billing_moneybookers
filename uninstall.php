<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * /uninstall.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers
 * @since 1.2.6
 */
$billingService = BOL_BillingService::getInstance();

$billingService->deleteConfig('ocsbillingmoneybookers', 'customerId');
$billingService->deleteConfig('ocsbillingmoneybookers', 'secret');
$billingService->deleteConfig('ocsbillingmoneybookers', 'recipientDescription');
$billingService->deleteConfig('ocsbillingmoneybookers', 'sandboxMode');
$billingService->deleteConfig('ocsbillingmoneybookers', 'language');

$billingService->deleteGateway('ocsbillingmoneybookers');