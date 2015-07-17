<?php

/**
 * Pagantis Checkout Controller
 * 
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_PagantisController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer chooses Pagantis on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $session = Mage::getSingleton('checkout/session');
        $order = Mage::getModel('sales/order')->load($session->getLastOrderId());
        $order->setState($state, $state, Mage::helper('pagantis_pagantis')->__('Redirected to Pagantis'), false);
        $order->save();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * When customer cancel payment from CECA (UrlKO)
     * 
     */
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        //Mage::getSingleton(‘core/session’)->addError(‘Error message’);
        $session->addError('Lo sentimos, se ha producido algún error en el pago, le agradeceríamos que volviera a intentarlo.');
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            Mage::helper('pagantis_pagantis')->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }

    public function notificationAction(){
        //$json = json_decode(file_get_contents(Mage::getBaseDir().'/charge.created.txt'),true);
        //Notification url mush be like http://mydomain.com/pagantis/pagantis/notification

        $json = file_get_contents('php://input');
        $temp = json_decode($json,true);
        $data = $temp['data'];
        $orderId = $data['order_id'];
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        Mage::log('Pagantis notification received for orderId '.$orderId);
        //Mage::log($data,null,'logfile.txt');
        if ($order->getId()) {
            switch ($temp['event']) {
                case 'charge.created':
                    $order->setPagantisTransaction($data['id']);
                    $order->save();
                    $this->_processOrder($order);
                    break;
                case 'charge.failed':
                    $order->setPagantisTransaction($data['id']);
                    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED,true);
                    $order->save();
                    break;
            }
        }
    }

    /**
     * When customer returns from Pagantis (UrlOK)
     * The order information at this point is in POST
     * variables. Order processing is done on callbackAction
     */
    public function successAction()
    {
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    public function callbackAction()
    {
        $params = $this->getRequest()->getPost();
        $response = Mage::getModel('pagantis_pagantis/webservice_response', $params);
        $request = Mage::helper('pagantis_pagantis')->getRequest();

        if ($request->checkResponseSignature($response)) {
            // Process order
            $this->_processOrder($response->getOrder(), $response);
        } else {
            // Log invalid signature and redirect to home
            Mage::log('Pagantis: invalid signature on callback', Zend_Log::WARN);
            $this->_redirect('');
        }
    }

    /**
     * Process order
     * 
     * @param $order
     */
    private function _processOrder($order)
    {
        try {
            $sendMail = (int) Mage::getStoreConfig('payment/pagantis/sendmail');
            $createInvoice = (int) Mage::getStoreConfig('payment/pagantis/invoice');
            if ($order->getId()) {
                //if ($response->isDsValid()) {
                    $orderStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
                    if ($order->canInvoice() && $createInvoice) {
                        $invoice = $this->_createInvoice($order);
                        $comment = Mage::helper('pagantis_pagantis')->__('Transacción authorizada. Creada factura %s', $invoice->getIncrementId());
                    } else {
                        $comment = Mage::helper('pagantis_pagantis')->__('Transacción authorizada, factura no creada');
                    }
//                } else {
//                    $orderStatus = Mage_Sales_Model_Order::STATE_CANCELED;
//                    $comment = Mage::helper('yameveo_ceca')->__($response->getErrorMessage());
//                }
                $order->setState($orderStatus, $orderStatus, $comment, true);
                $order->save();
                if ($sendMail) {
                    if ($orderStatus == Mage_Sales_Model_Order::STATE_PROCESSING) {
                        $order->sendNewOrderEmail();
                    } else {
                        $order->sendOrderUpdateEmail(true);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Create an invoice for the order and send an email
     * 
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     */
    private function _createInvoice($order)
    {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        if (!$invoice->getTotalQty()) {
            Mage::throwException(Mage::helper('core')->__('No se puede crear una factura sin productos.'));
        }

        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
        $invoice->register();
        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

        $transactionSave->save();
        $invoice->sendEmail();
        return $invoice;
    }

}
