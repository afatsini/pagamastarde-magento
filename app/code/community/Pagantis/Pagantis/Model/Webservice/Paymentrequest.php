<?php

/**
 * Class Pagantis_Pagantis_Model_Webservice_Request
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */

class Pagantis_Pagantis_Model_Webservice_Paymentrequest
{
    const BASE = 'pagantis/pagantis';
    const URL_PAYMENT_REQUEST = 'https://psp.pagantis.com/api/1/charges/';

    /**
     * @var string $_urlPagantis Redirect url for payment
     */
    protected $_urlPagantis;

    /**
     * @var string $_amount Order Amount
     */
    protected $_bearer;

    /**
     * @var string $_currency Order Amount
     */
    protected $_transaction;



    public function __construct()
    {
        $this->_urlPagantis = self::URL_PAYMENT_REQUEST;
    }

    /**
     * Assign bearer for call
     * @param string $bearer
     * @throws Exception
     */
    public function setBearer($bearer = '')
    {
        if (strlen(trim($bearer)) > 0) {
            $this->_bearer = $bearer;
        } else {
            throw new \Exception('Missing bearer for call');
        }
    }

    /**
     * Assign transaction for call
     * @param string $transaction
     * @throws Exception
     */
    public function setTransaction($transaction = '')
    {
        if (strlen(trim($transaction)) > 0) {
            $this->_transaction = $transaction;
        } else {
            throw new \Exception('Missing transaction for call');
        }
    }

    public function getTransaction(){
        return $this->_transaction;
    }
    public function getBearer(){
        return $this->_bearer;
    }
    public function getUrlPagantis(){
        return $this->_urlPagantis;
    }
}
