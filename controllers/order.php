<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
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