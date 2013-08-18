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
 * /deactivate.php
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_billing_moneybookers
 * @since 1.2.6
 */
BOL_BillingService::getInstance()->deactivateGateway('ocsbillingmoneybookers');