<?php

/**
 * Payment class
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */

class Pagantis_Pagantis_Model_Payment extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'pagantis';
    protected $_formBlockType = 'pagantis_pagantis/form';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_config = null;

    const PAYMENT_UNIQUE = 'unico';
    const PAYMENT_LOAN = 'financiacion';
    const PMT_URL = 'https://pmt.pagantis.com/v1/installments/';

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Create main block for standard form
     *
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock($this->_formBlockType, $name)
            ->setMethod('pagantis')
            ->setPayment($this->getPayment())
            ->setTemplate('pagantis/form.phtml');

        return $block;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('pagantis/pagantis/redirect', array('_secure' => true));
    }

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $paymentDetailArray = Mage::app()->getRequest()->getPost('paymentdetail');
        $paymentDetail = $paymentDetailArray[0];
        $this->getCheckout()->setPaymentMethodDetail($paymentDetail);
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    public function getFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $paymentDetails = $this->getCheckout()->getPaymentMethodDetail();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        // For EUR last 2 position are considered decimals
        $amount = $order->getTotalDue() * 100;
        $orderId = $order->increment_id;

        $localeCode = Mage::app()->getLocale()->getLocaleCode();
        switch($paymentDetails) {
            case self::PAYMENT_UNIQUE:
                $request = Mage::helper('pagantis_pagantis')->getRequest($amount, $orderId, $localeCode);
                break;
            case self::PAYMENT_LOAN:
                $request = Mage::helper('pagantis_pagantis')->getRequestloan($amount, $orderId, $localeCode);
                break;
        }
        return $request->toArray();
    }


}
