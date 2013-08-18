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


function ocsbillingmoneybookers_add_access_exception( BASE_CLASS_EventCollector $e )
{
    $e->add(array('controller' => 'OCSBILLINGMONEYBOOKERS_CTRL_Order', 'action' => 'notify'));
}

OW::getEventManager()->bind('base.members_only_exceptions', 'ocsbillingmoneybookers_add_access_exception');
OW::getEventManager()->bind('base.password_protected_exceptions', 'ocsbillingmoneybookers_add_access_exception');
OW::getEventManager()->bind('base.splash_screen_exceptions', 'ocsbillingmoneybookers_add_access_exception');