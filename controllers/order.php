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
 * Moneybookers order pages controller
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers.controllers
 * @since 1.2.6
 */
class OCSBILLINGMONEYBOOKERS_CTRL_Order extends OW_ActionController
{
    public function form()
    {
        $billingService = BOL_BillingService::getInstance();
        $adapter = new OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter();
        $lang = OW::getLanguage();

        $sale = $billingService->getSessionSale();

        if ( !$sale )
        {
            $url = $billingService->getSessionBackUrl();
            if ( $url != null )
            {
                OW::getFeedback()->warning($lang->text('base', 'billing_order_canceled'));
                $billingService->unsetSessionBackUrl();
                $this->redirect($url);
            }
            else 
            {
                $this->redirect($billingService->getOrderFailedPageUrl());
            }
        }

        $formId = uniqid('order_form-');
        $this->assign('formId', $formId);

        $js = '$("#' . $formId . '").submit()';
        OW::getDocument()->addOnloadScript($js);

        $fields = $adapter->getFields(array('hash' => $sale->hash));
        $this->assign('fields', $fields);

        if ( $billingService->prepareSale($adapter, $sale) )
        {
            $sale->totalAmount = floatval($sale->totalAmount);
            $this->assign('sale', $sale);

            $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate('blank');
            OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);

            $billingService->unsetSessionSale();
        }
        else
        {
            $productAdapter = $billingService->getProductAdapter($sale->entityKey);

            if ( $productAdapter )
            {
                $productUrl = $productAdapter->getProductOrderUrl();
            }
            
            OW::getFeedback()->warning($lang->text('base', 'billing_order_init_failed'));
            $url = isset($productUrl) ? $productUrl : $billingService->getOrderFailedPageUrl();
            
            $this->redirect($url);
        }
    }

    public function notify()
    {
        $log = OW::getLogger('ocsbillingmoneybookers');
        $log->addEntry(print_r($_REQUEST, true), 'notify.data');
        $log->writeLog();
        
        if ( empty($_REQUEST['custom']) )
        {
            exit;
        }

        $hash = trim($_REQUEST['custom']);

        $transId = !empty($_REQUEST['rec_payment_id']) ? trim($_REQUEST['rec_payment_id']) : trim($_REQUEST['mb_transaction_id']);
        $status = trim($_REQUEST['status']);
        
        $amount = !empty($_REQUEST['amount'])? $_REQUEST['amount'] : $_REQUEST['rec_amount'];
        $sig = trim($_REQUEST['md5sig']);
        
        $billingService = BOL_BillingService::getInstance();
        
        $gwKey = OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter::GATEWAY_KEY;
        $merchantId = $billingService->getGatewayConfigValue($gwKey, 'merchantId');
        $secret = $billingService->getGatewayConfigValue($gwKey, 'secret');
        
        $slug = strtoupper(md5($merchantId . $_REQUEST['transaction_id'] . strtoupper(md5($secret)) . $_REQUEST['mb_amount'] . $_REQUEST['mb_currency'] . $status));
        
        if ( $slug !== $sig )
        {
        	exit("SIG_MISMATCH");
        }
        
        if ( $status == '2' )
        {
        	$sale = $billingService->getSaleByHash($hash);

            if ( !$sale || !mb_strlen($transId) )
            {
                exit("NOT_FOUND");
            }
            
            $adapter = new OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter();
        	
        	if ( empty($_REQUEST['rec_payment_id']) )
        	{
                if ( !$billingService->saleDelivered($transId, $sale->gatewayId) )
                {
                    $sale->transactionUid = $transId;

                    if ( $billingService->verifySale($adapter, $sale) )
                    {
                        $sale = $billingService->getSaleById($sale->id);
                                
                        $productAdapter = $billingService->getProductAdapter($sale->entityKey);

                        if ( $productAdapter )
                        {
                            $billingService->deliverSale($productAdapter, $sale);
                        }
                    }
                }
        	}
        	else 
        	{
                $rebillTransId = $transId;

                $gateway = $billingService->findGatewayByKey($gwKey);
                        
                if ( $billingService->saleDelivered($rebillTransId, $gateway->id) )
                {
                    exit("DELIVERED");
                }
                        
                $rebillSaleId = $billingService->registerRebillSale($adapter, $sale, $rebillTransId);

                if ( $rebillSaleId )
                {
                    $rebillSale = $billingService->getSaleById($rebillSaleId); 

                    $productAdapter = $billingService->getProductAdapter($rebillSale->entityKey);
                    if ( $productAdapter )
                    {
                        $billingService->deliverSale($productAdapter, $rebillSale);
                    }
                }
        	}
        }
        
        exit("REGISTERED");
    }

    public function completed( array $params )
    {
        $hash = trim($params['custom']);

        $this->redirect(BOL_BillingService::getInstance()->getOrderCompletedPageUrl($hash));
    }
    
    public function canceled( array $params )
    {
    	$hash = trim($params['custom']);
    	
        $this->redirect(BOL_BillingService::getInstance()->getOrderCancelledPageUrl($hash));
    }
}