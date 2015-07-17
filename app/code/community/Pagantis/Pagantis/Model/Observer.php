<?php

/**
 * Observer for cron jobs
 *
 * @package    Pagantis_Pagantis
 * @author     Yameveo <support@yameveo.com>
 * @link       http://www.yameveo.com
 */
class Pagantis_Pagantis_Model_Observer
{

    const PAGANTIS_TR_ERROR = 'Error';


    public function exportOrders()
    {
        $start = $this->chrono();
        $startGetCollection = $this->chrono();

        $orders = Mage::helper('pagantis_pagantis')->getOrdersToExport(array(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));//,Mage_Sales_Model_Order::STATE_PROCESSING));//, array(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,Mage_Sales_Model_Order::STATE_PROCESSING));

        $endGetCollection = $this->chrono();
        $chronoCollection = round($endGetCollection - $startGetCollection, 3);
        Mage::log("Loading orders collection executed in $chronoCollection seconds.", Zend_Log::DEBUG, $this->_logFile);
        $ordersCount = $orders->count();
        Mage::log("Exporting $ordersCount orders", Zend_Log::DEBUG, $this->_logFile);

        $count = 1;
        $result = array();
        $orderCanceled = 0;
        $orderProcessing = 0;
        foreach ($orders as $order) {
            $transaction = $order->getPagantisTransaction();
            if ($transaction) {
                $paymentRequestResult = Mage::helper('pagantis_pagantis')->getTransactionInfo($transaction);
                if (!$paymentRequestResult['paid']) {
                    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED,true);
                    $order->save();
                    $orderCanceled++;
                } else {
                    // @todo move this try in helper
                    try {
                        $sendMail = (int) Mage::getStoreConfig('payment/pagantis/sendmail');
                        $createInvoice = (int) Mage::getStoreConfig('payment/pagantis/invoice');
                        if ($order->getId()) {
                            //if ($response->isDsValid()) {
                            $orderStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
                            if ($order->canInvoice() && $createInvoice) {
                                $invoice = $this->_createInvoice($order);
                                $comment = Mage::helper('pagantis_pagantis')->__('Authorized transaction. Created invoice %s', $invoice->getIncrementId());
                            } else {
                                $comment = Mage::helper('pagantis_pagantis')->__('Authorized transaction, but invoice not created');
                            }
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
                        $orderProcessing++;
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
        return "Updated " . $orderCanceled . " orders in steta Canceled and " . $orderProcessing . " orders in Processing";
    }

    /**
     * Create an invoice for the order and send an email
     * @todo move to helper
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     */
    private function _createInvoice($order)
    {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        if (!$invoice->getTotalQty()) {
            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
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

    /**
     * Returns the actual microtime in seconds
     * @return float seconds
     */
    protected function chrono()
    {
        list($msec, $sec) = explode(' ', microtime());
        return ((float)$msec + (float)$sec);
    }



}
