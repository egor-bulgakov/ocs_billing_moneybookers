<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * /init.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers
 * @since 1.2.6
 */
OW::getRouter()->addRoute(
    new OW_Route('ocsbillingmoneybookers.order_form', 'ocs-billing-moneybookers/order', 'OCSBILLINGMONEYBOOKERS_CTRL_Order', 'form')
);
OW::getRouter()->addRoute(
    new OW_Route('ocsbillingmoneybookers.notify', 'ocs-billing-moneybookers/order/notify', 'OCSBILLINGMONEYBOOKERS_CTRL_Order', 'notify')
);
OW::getRouter()->addRoute(
    new OW_Route('ocsbillingmoneybookers.completed', 'ocs-billing-moneybookers/order/completed/:hash', 'OCSBILLINGMONEYBOOKERS_CTRL_Order', 'completed')
);
OW::getRouter()->addRoute(
    new OW_Route('ocsbillingmoneybookers.canceled', 'ocs-billing-moneybookers/order/canceled/:hash', 'OCSBILLINGMONEYBOOKERS_CTRL_Order', 'canceled')
);
OW::getRouter()->addRoute(
    new OW_Route('ocsbillingmoneybookers.admin', 'admin/plugin/ocs-billing-moneybookers', 'OCSBILLINGMONEYBOOKERS_CTRL_Admin', 'index')
);

function ocsbillingmoneybookers_add_admin_notification( BASE_CLASS_EventCollector $coll )
{
    $billingService = BOL_BillingService::getInstance();
    $gwKey = OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter::GATEWAY_KEY;
    
    if ( !mb_strlen($billingService->getGatewayConfigValue($gwKey, 'merchantId'))
        || !mb_strlen($billingService->getGatewayConfigValue($gwKey, 'merchantEmail')) 
        || !mb_strlen($billingService->getGatewayConfigValue($gwKey, 'secret')) )
    {
        $coll->add(
            OW::getLanguage()->text(
                'ocsbillingmoneybookers', 
                'plugin_configuration_notice', 
                array('url' => OW::getRouter()->urlForRoute('ocsbillingmoneybookers.admin'))
            )
        );
    }
}

OW::getEventManager()->bind('admin.add_admin_notification', 'ocsbillingmoneybookers_add_admin_notification');