<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Moneybookers administration controller
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers.controllers
 * @since 1.2.6
 */
class OCSBILLINGMONEYBOOKERS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $billingService = BOL_BillingService::getInstance();
        $gwKey = OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter::GATEWAY_KEY;
        $language = OW::getLanguage();

        $form = new MoneybookersConfigForm();
        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
        	$values = $form->getValues();
        	
            $billingService->setGatewayConfigValue($gwKey, 'merchantId', $values['merchantId']);
            $billingService->setGatewayConfigValue($gwKey, 'merchantEmail', $values['merchantEmail']);
            $billingService->setGatewayConfigValue($gwKey, 'secret', $values['secret']);
            $billingService->setGatewayConfigValue($gwKey, 'sandboxMode', $values['sandboxMode']);
            $billingService->setGatewayConfigValue($gwKey, 'recipientDescription', $values['recipientDescription']);
            $billingService->setGatewayConfigValue($gwKey, 'language', $values['language']);
        
            OW::getFeedback()->info($language->text('ocsbillingmoneybookers', 'settings_updated'));
            $this->redirect();
        }

        $adapter = new OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter();
        $this->assign('logoUrl', $adapter->getLogoUrl());

        $gateway = $billingService->findGatewayByKey($gwKey);
        $this->assign('gateway', $gateway);

        $this->assign('activeCurrency', $billingService->getActiveCurrency());

        $supported = $billingService->currencyIsSupported($gateway->currencies);
        $this->assign('currSupported', $supported);
        
        $logo = OW::getPluginManager()->getPlugin('ocsbillingmoneybookers')->getStaticUrl() . 'img/oxwallcandystore-logo.jpg';
        $this->assign('logo', $logo);

        $this->setPageHeading(OW::getLanguage()->text('ocsbillingmoneybookers', 'config_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_app');
    }
}

class MoneybookersConfigForm extends Form
{
    public function __construct()
    {
        parent::__construct('moneybookers-config-form');

        $language = OW::getLanguage();
        $billingService = BOL_BillingService::getInstance();
        $gwKey = OCSBILLINGMONEYBOOKERS_CLASS_MoneybookersAdapter::GATEWAY_KEY;

        $merchantId = new TextField('merchantId');
        $merchantId->setValue($billingService->getGatewayConfigValue($gwKey, 'merchantId'));
        $merchantId->setRequired(true);
        $merchantId->setLabel($language->text('ocsbillingmoneybookers', 'merchant_id'));
        $this->addElement($merchantId);
        
        $merchantEmail = new TextField('merchantEmail');
        $merchantEmail->setValue($billingService->getGatewayConfigValue($gwKey, 'merchantEmail'));
        $merchantEmail->setRequired(true);
        $merchantEmail->setLabel($language->text('ocsbillingmoneybookers', 'merchant_email'));
        $this->addElement($merchantEmail);
        
        $secret = new TextField('secret');
        $secret->setValue($billingService->getGatewayConfigValue($gwKey, 'secret'));
        $secret->setRequired(true);
        $secret->setLabel($language->text('ocsbillingmoneybookers', 'secret'));
        $this->addElement($secret);

        $sandboxMode = new CheckboxField('sandboxMode');
        $sandboxMode->setValue($billingService->getGatewayConfigValue($gwKey, 'sandboxMode'));
        $sandboxMode->setLabel($language->text('ocsbillingmoneybookers', 'sandbox_mode'));
        $this->addElement($sandboxMode);
        
        $desc = new TextField('recipientDescription');
        $desc->setValue($billingService->getGatewayConfigValue($gwKey, 'recipientDescription'));
        $desc->setLabel($language->text('ocsbillingmoneybookers', 'recipient_description'));
        $this->addElement($desc);
        
        $lang = new Selectbox('language');
        $lang->setLabel($language->text('ocsbillingmoneybookers', 'language'));
        $lang->addOptions(array('EN'=>'EN','DE'=>'DE','ES'=>'ES','FR'=>'FR','IT'=>'IT','PL'=>'PL','GR'=>'GR','RO'=>'PO','RU'=>'RU','TR'=>'TR','CN'=>'CN','CZ'=>'CZ','NL'=>'NL','DA'=>'DA','SV'=>'SV','FI'=>'FI'));
        $lang->setRequired(true);
        $lang->setValue($billingService->getGatewayConfigValue($gwKey, 'language'));
        $this->addElement($lang);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('ocsbillingmoneybookers', 'btn_save'));
        $this->addElement($submit);
    }
}