<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
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