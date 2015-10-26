<?php

/**
 * Pagantis_Pagantis Helper
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $languages = array(
        'es_ES' => 'es',
        'en_US' => 'en',
        'en_UK' => 'en',
        'ca_ES' => 'ca',
        'eu_ES' => 'es',
        'gl_ES' => 'es',
        'fr_FR' => 'fr',
        'it_IT' => 'it',
        'ru_RU' => 'ru'
    );

    public function __construct()
    {
        $this->_config = Mage::getStoreConfig('payment/pagantis');
    }

    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        $lastOrderId = $this->_getCheckoutSession()->getLastRealOrderId();
        if ($lastOrderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($lastOrderId);
            if ($order->getId()) {
                $quote = $this->_getQuote($order->getQuoteId());
                if ($quote->getId()) {
                    $quote->setIsActive(1)
                            ->setReservedOrderId(null)
                            ->save();
                    $this->_getCheckoutSession()
                            ->replaceQuote($quote)
                            ->unsLastRealOrderId();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return checkout session instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return sales quote instance for specified ID
     *
     * @param int $quoteId Quote identifier
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }


    /**
     * Returns a Request object with common parameters
     * @todo Retrieve language, currency based on config
     * @param float $amount
     * @param string $orderId
     * @param string $localeCode TBD
     * @return Pagantis_Pagantis_Model_Webservice_Request
     */
    public function getRequest($amount, $orderId, $localeCode)
    {
        $language = $this->getConsumerLanguage($localeCode);
        $request = Mage::getModel('pagantis_pagantis/webservice_request');
        $request->setAmount($amount);
        $request->setLanguagePagantis($language);
        $request->setUrlPagantis($this->_config['url_pagantis']);
        $request->setAuthMethod($this->_config['auth_method']);
        $request->setOrderId($orderId);
        $request->setAmount($amount);
        $request->setDiscount($this->_config['discount']);
        switch($this->_config['environment']) {
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_TESTING:
                $request->setAccountCode($this->_config['account_code_test']);
                $request->setAccountKey($this->_config['account_key_test']);
                //$request->setAccountApiKey($this->_config['account_api_key_test']);
                break;
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_PRODUCTION:
                $request->setAccountCode($this->_config['account_code_real']);
                $request->setAccountKey($this->_config['account_key_real']);
                //$request->setAccountApiKey($this->_config['account_api_key_real']);
                break;
        }
        $request->setUrlOk();
        $request->setUrlKo();
        $request->setCacllbackUrl();
        $request->setFirma();
        return $request;
    }

    /**
     * Returns a Request object with common parameters
     * @todo Retrieve language, currency based on config
     * @param float $amount
     * @param string $orderId
     * @param string $localeCode TBD
     * @return Pagantis_Pagantis_Model_Webservice_Request
     */
    public function getRequestloan($amount, $orderId, $localeCode)
    {
        $language = $this->getConsumerLanguage($localeCode);
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $addressId = $order->getShippingAddress()->getId();


        $request = Mage::getModel('pagantis_pagantis/webservice_requestloan');
        $request->setOrderId($orderId);
        $request->setAmount($amount);
        $request->setLanguagePagantis($language);
        //$request->setUrlPagantis($this->_config['url_pagantis_mastarde']);
        $request->setUrlPagantis (Pagantis_Pagantis_Model_Payment::PMT_URL);
        $request->setUserData($addressId);
        $request->setOrderItems($orderId);
        $request->setAmount($amount);
        $request->setDiscount($this->_config['discount']);
        switch($this->_config['environment']) {
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_TESTING:
                $request->setAccountCode($this->_config['account_code_test']);
                $request->setAccountKey($this->_config['account_key_test']);
                break;
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_PRODUCTION:
                $request->setAccountCode($this->_config['account_code_real']);
                $request->setAccountKey($this->_config['account_key_real']);
                break;
        }
        $request->setUrlOk();
        $request->setUrlKo();
        $request->setCacllbackUrl();
        $request->setFirma();

        return $request;
    }

    /**
     * Returns a Request object with common parameters
     * @todo Retrieve language, currency based on config
     * @param float $amount
     * @param string $orderId
     * @param string $localeCode TBD
     * @return Pagantis_Pagantis_Model_Webservice_Request
     */
    public function getTransactionInfo($transaction)
    {
        $paymentRequest = Mage::getModel('pagantis_pagantis/webservice_paymentrequest');
        switch($this->_config['environment']) {
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_TESTING:
                $paymentRequest->setBearer($this->_config['account_api_key_test']);
                break;
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_PRODUCTION:
                $paymentRequest->setBearer($this->_config['account_api_key_real']);
                break;
        }
        $paymentRequest->setTransaction($transaction);
        $client = Mage::getModel('pagantis_pagantis/webservice_client');
        $result = $client->getCurlResultPaymentRequest($paymentRequest);
        return $result;
    }


    public function getUrlPagantis() {

        return Pagantis_Pagantis_Model_Payment::PMT_URL;
        //allways same url, no need to make this check
        /*
        $paymentDetails = $this->_getCheckoutSession()->getPaymentMethodDetail();
        switch($paymentDetails) {
            case Pagantis_Pagantis_Model_Payment::PAYMENT_UNIQUE:
                return $this->_config['url_pagantis'];
                break;
            case Pagantis_Pagantis_Model_Payment::PAYMENT_LOAN:
                return $this->_config['url_pagantis_mastarde'];
                break;
        }
        */
    }

    /**
     * Returns a string with the corresponding CECA consumer language, given a ISO locale code
     * Eg. es_ES
     *
     * @param $localeCode
     * @return mixed
     */
    private function getConsumerLanguage($localeCode)
    {
        return $this->languages[$localeCode];
    }

    /**
     * @param $states
     * @param $statuses
     * @return mixed
     */
    public function getOrdersToExport($states)
    {
        $methodPayment = $this->_config['title'];

        $pendingProcessingOrdersPagantis = Mage::getModel('sales/order')
            ->getCollection()
            ->join(
                array('payment' => 'sales/order_payment'),
                'main_table.entity_id=payment.parent_id',
                array('payment_method' => 'payment.method')
            );
        $pendingProcessingOrdersPagantis->addFieldToFilter('payment.method',array('like'=>$methodPayment))
                                        ->addFieldToFilter('state',array('in'=>$states));

        return $pendingProcessingOrdersPagantis;
    }

}
