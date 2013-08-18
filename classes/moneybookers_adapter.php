<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the "License");
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://opensource.org/licenses/CPAL-1.0. Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License.
 * The Initial Developer of the Original Code is Oxwall CandyStore (http://oxcandystore.com/).
 * All portions of the code written by Oxwall CandyStore are Copyright (c) 2013. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2013 Oxwall CandyStore. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall CandyStore
 * Attribution URL: http://oxcandystore.com/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * Moneybookers billing gateway adapter class.
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers.classes
 * @since 1.2.6
 */
class OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter implements OW_BillingAdapter
{
    const GATEWAY_KEY = 'ocsbillingmoneybookers';

    /**
     * @var BOL_BillingService
     */
    private $billingService;

    public function __construct()
    {
        $this->billingService = BOL_BillingService::getInstance();
    }

    public function prepareSale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    public function verifySale( BOL_BillingSale $sale )
    {
        // ... gateway custom manipulations

        return $this->billingService->saveSale($sale);
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getFields($params)
     */
    public function getFields( $params = null )
    {
        $router = OW::getRouter();

        return array(
            'pay_to_email' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'merchantEmail'),
            'recipient_description' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'recipientDescription'),
            'return_url' => $router->urlForRoute('ocsbillingmoneybookers.completed', array('hash' => $params['hash'])),
            'cancel_url' => $router->urlForRoute('ocsbillingmoneybookers.canceled', array('hash' => $params['hash'])),
            'status_url' => $router->urlForRoute('ocsbillingmoneybookers.notify'),
            'formActionUrl' => $this->getOrderFormActionUrl(),
            'language' => $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'language')
        );
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getOrderFormUrl()
     */
    public function getOrderFormUrl()
    {
        return OW::getRouter()->urlForRoute('ocsbillingmoneybookers.order_form');
    }

    /**
     * (non-PHPdoc)
     * @see ow_core/OW_BillingAdapter#getLogoUrl()
     */
    public function getLogoUrl()
    {
        $plugin = OW::getPluginManager()->getPlugin('ocsbillingmoneybookers');

        return $plugin->getStaticUrl() . 'img/moneybookers_logo.jpg';
    }

    /**
     * Returns Moneybookers gateway script url (sandbox or live)
     * 
     * @return string
     */
    private function getOrderFormActionUrl()
    {
        $sandboxMode = $this->billingService->getGatewayConfigValue(self::GATEWAY_KEY, 'sandboxMode');

        return $sandboxMode ? 'http://www.moneybookers.com/app/test_payment.pl' : 'https://www.moneybookers.com/app/payment.pl';
    }
}