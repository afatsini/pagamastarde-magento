<?php

/**
 * Class Pagantis_Pagantis_Model_Webservice_Client
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */

class Pagantis_Pagantis_Model_Webservice_Client
{

    // Environments
    const ENV_TESTING = 'testing';
    const ENV_PRODUCTION = 'production';




    private $_pathConfig;

    /**
     * Pagantis_Pagantis_Model_Webservice_Client constructor
     * @param array $path_config
     */
    public function __construct($path_config = null)
    {
        if ($path_config != null) {
            $this->_pathConfig = $path_config['config'];
        }
        $this->_session = Mage::getSingleton('checkout/session');
    }
    
    /**
     * Send a request and process response
     *
     * @param Pagantis_Pagantis_Model_Webservice_Request $request
     * @return Pagantis_Pagantis_Model_Webservice_Response
     */
    public function makeRequest(Pagantis_Pagantis_Model_Webservice_Request $request)
    {
        $response = $this->sendRequest($request);
        return $this->processResponse($response);
    }

    /**
     * Send a request and process response
     *
     * @param Pagantis_Pagantis_Model_Webservice_Request $request
     * @return array
     */
    public function getCurlResultPaymentRequest(Pagantis_Pagantis_Model_Webservice_PaymentRequest $paymentRequest)
    {
        $bearer = (string)$paymentRequest->getBearer();
        $urlApi = (string)$paymentRequest->getUrlPagantis().$paymentRequest->getTransaction();

        $crl = curl_init();

        $header = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Bearer '.$bearer;

        curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
        curl_setopt($crl,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($crl,CURLOPT_URL, $urlApi);
        $result = json_decode(curl_exec($crl),true);
        curl_close($crl);
        return $result['response'];
    }

    /**
     * Send a request
     * 
     * @param Pagantis_Pagantis_Model_Webservice_Request $request
     * @return Object
     */
    private function sendPaymentRequest(Pagantis_Pagantis_Model_Webservice_PaymentRequest $request)
    {

        $xmlRequest = $request->toXml();
        $response = $this->trataPeticion(array(
            self::SOAP_REQUEST_WRAPPER => $xmlRequest
        ));

        return $response;
    }

    /**
     * Process response
     * 
     * @param Pagantis_Pagantis_Model_Webservice_Response $response
     */
    /*private function processResponse($response)
    {
        $response = new Pagantis_Pagantis_Model_Webservice_Response($response->trataPeticionReturn);
        return $response;
    }*/
    
    public function getUrl()
    {
        $conf = Mage::getStoreConfig('payment/pagantis');
        $environment = $conf['environment'];
        switch($environment){
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_TESTING:
                return $conf['url_pagantis_test'];
                break;
            case Pagantis_Pagantis_Model_Webservice_Client::ENV_PRODUCTION:
                return $conf['url_pagantis_real'];
                break;
        }
    }

}
