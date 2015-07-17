<?php

/**
 * Class Pagantis_Pagantis_Model_Webservice_Request
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */

class Pagantis_Pagantis_Model_Webservice_Requestloan
{
    const BASE = 'pagantis/pagantis';

    /**
     * @var string $_urlPagantis Redirect url for payment
     */
    protected $_urlPagantis;

    /**
     * @var string $_amount Order Amount
     */
    protected $_amount;

    /**
     * @var string $_currency Order Amount
     */
    protected $_currency;

    /**
     * @var string $_orderId Increment order Id
     */
    protected $_orderId;
    /**
     * @var string $_languagePagantis Force language on bank page
     */
    protected $_languagePagantis;

    /**
     * @var string $_authMethod Authentication method for payment redirect.
     */
    protected $_authMethod;

    /**
     * @var string $_accountCode
     */
    protected $_accountCode;

    /**
     * @var string $accountKey
     */
    protected $_accountKey;


    /**
     * @var string $_urlOk Requerido 500 URL completa.
     */
    protected $_urlOk;

    /**
     *
     * @var string $_urlKo Requerido 500 URL completa.
     */
    protected $_urlKo;



    /**
     * @var string $_firma created by clave_de_firma + account_id + order_id + amount + currency + ok_url + nok_url
     */
    protected $_firma;

    /**
     * @var array
     */
    protected $_userData;

    /**
     * @var array
     */
    protected $_items;


    public function __construct()
    {
        $this->_languagePagantis = $this->setLanguagePagantis(); //Por defecto español
        $this->_currency = $this->setCurrency();
    }

    /**
     * Return the array version of the Request to be sent through the form
     *
     * @return string
     */
    public function toArray()
    {
        $array = array();
        $array['order_id'] = $this->_orderId;
        $array['amount'] = $this->_amount;
        $array['currency'] = $this->_currency;

        $array['ok_url'] = $this->_urlOk;
        $array['nok_url'] = $this->_urlKo;

        $array['locale'] = $this->_languagePagantis;

        $array['full_name'] = $this->_userData['full_name'];
        $array['email'] = $this->_userData['email'];
        $array['address[street]'] = $this->_userData['street'];
        $array['address[city]'] = $this->_userData['city'];
        $array['address[province]'] = $this->_userData['province'];
        $array['address[zipcode]'] = $this->_userData['zipcode'];
        $array['dni'] = $this->_userData['dni'];

        foreach($this->_items as $key => $value){
            $array['items[' . $key . '][description]'] = $value['description'];
            $array['items[' . $key . '][quantity]'] = $value['quantity'];
            $array['items[' . $key . '][amount]'] = $value['amount'];
        }

        $array['account_id'] = $this->_accountCode;
        $array['signature'] = $this->_firma;

        return $array;
    }

    /**
     * Assign url for redirect
     * @param string $urlPagantis
     * @throws Exception
     */
    public function setUrlPagantis($urlPagantis = '')
    {
        if (strlen(trim($urlPagantis)) > 0) {
            $this->_urlPagantis = $urlPagantis;
        } else {
            throw new \Exception('Missing url for redirect to page bank');
        }
    }

    /**
     * Assign url for redirect
     * @param string $urlPagantis
     * @throws Exception
     */
    public function setOrderId($orderId = '')
    {
        if (strlen(trim($orderId)) > 0) {
            $this->_orderId = $orderId;
        } else {
            throw new \Exception('Missing orderId');
        }
    }

    /**
     * Assign currency for redirect
     * @param string $currency
     * @throws Exception
     */
    public function setCurrency()
    {
        return Pagantis_Pagantis_Model_Currency::EUR;
    }

    /**
     * Assign url for redirect
     * @param string $urlPagantis
     * @throws Exception
     */
    public function setAmount($amount = '')
    {
        if (strlen(trim($amount)) > 0) {
            $this->_amount = $amount;
        } else {
            throw new \Exception('Missing amount');
        }
    }

    /**
     * Assign language for bank page
     * @param string $language Language for bank page
     */
    public function setLanguagePagantis($language = 'es')
    {
        return $language;
    }

    /**
     * Assign user data
     * @param string $addressId
     * @throws Exception
     */
    public function setUserData($addressId)
    {
        if ($addressId) {
            $address = Mage::getModel('sales/order_address')->load($addressId);
            $street = $address->getStreet();
            if ($street){
                $this->_userData['street'] = $street[0];
            }
            $this->_userData['city'] = $address->getCity();
            $this->_userData['province'] = $address->getCity();
            $this->_userData['zipcode'] = $address->getPostcode();
            $this->_userData['dni'] = $address->getVatId();
            $this->_userData['full_name'] = $address->getFirstname() . ' ' . $address->getLastname();
            $this->_userData['email'] = $address->getEmail();
        } else {
            throw new \Exception('Missing user data info');
        }
    }

    /**
     * Assign user data
     * @param string $addressId
     * @throws Exception
     */
    public function setOrderItems($orderId)
    {
        if ($orderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $items = $order->getAllVisibleItems();
            $i = 0;
            foreach($items as $item){
                $amount = round($item->getPriceInclTax(),2);
                $quantity = round($item->getQtyOrdered());
                $this->_items[$i]['description'] = $item->getName();
                $this->_items[$i]['quantity'] = $quantity;
                $this->_items[$i]['amount'] = round($amount*$quantity,2);
                $i++;
            }
            $shippingAmount = round($order->getShippingAmount(),2);
            if($shippingAmount){
                $this->_items[$i]['description'] = "Gastos de envío";
                $this->_items[$i]['quantity'] = "1";
                $this->_items[$i]['amount'] = $shippingAmount;
                $i++;
            }
            $discountAmount = round($order->getBaseDiscountAmount(),2);
            if($discountAmount){
                $this->_items[$i]['description'] = "Descuento";
                $this->_items[$i]['quantity'] = "1";
                $this->_items[$i]['amount'] = $discountAmount;
            }

        } else {
            throw new \Exception('Missing user data info');
        }
    }

    /**
     * Assign account code
     * @param string $accountCode
     * @throws Exception
     */
    public function setAccountCode($accountCode=''){
        if (strlen(trim($accountCode)) > 0) {
            $this->_accountCode = $accountCode;
        } else {
            throw new \Exception('Missing account code');
        }
    }

    /**
     * Assign account key
     * @param string $accountKey
     * @throws Exception
     */
    public function setAccountKey($accountKey=''){
        if (strlen(trim($accountKey)) > 0) {
            $this->_accountKey = $accountKey;
        } else {
            throw new \Exception('Missing account key');
        }
    }


    /**
     * @param string $urlok
     * @throws Exception
     */
    public function setUrlOk()
    {
        $urlOk = $this->getUrl('success');
        if (strlen(trim($urlOk)) > 0) {
            $this->_urlOk = $urlOk;
        } else {
            throw new \Exception('UrlOk not defined');
        }

    }

    /**
     * @param string $urlnok
     * @throws Exception
     */
    public function setUrlKo($urlKo = '')
    {
        $urlKo = $this->getUrl('cancel');
        if (strlen(trim($urlKo)) > 0) {
            $this->_urlKo = $urlKo;
        } else {
            throw new \Exception('UrlKo not defined');
        }
    }


    /**
     * Firm generation
     * Generated with SHA1 of _accountKey + _accountCode + _orderId + _amount + _currency + _urlOk + _urlKo
     * @throws Exception
     * @return string
     */
    public function setFirma()
    {
        $textToEncode = $this->_accountKey . $this->_accountCode . $this->_orderId . $this->_amount . $this->_currency  . $this->_urlOk . $this->_urlKo;

        if (strlen(trim($textToEncode)) > 0) {
            // Retrieve del SHA1
            $this->_firma = sha1($textToEncode);
        } else {
            throw new Exception('Missing SHA1');
        }
    }

    //Utilities
    //http://stackoverflow.com/a/9111049/444225
    private function priceToSQL($price)
    {
        $price = preg_replace('/[^0-9\.,]*/i', '', $price);
        $price = str_replace(',', '.', $price);

        if (substr($price, -3, 1) == '.') {
            $price = explode('.', $price);
            $last = array_pop($price);
            $price = join($price, '') . '.' . $last;
        } else {
            $price = str_replace('.', '', $price);
        }

        return $price;
    }

    public function getUrl($path)
    {
        $url = Mage::getUrl(self::BASE . "/$path");
        return Mage::getModel('core/url')->sessionUrlVar($url);
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     * @return string
     */
    private function underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        return $result;
    }

    /**
     * Checks if received response is correct
     * Clave_encriptacion+MerchantID+AcquirerBIN+TerminalID+Num_operacion+Importe+TipoMoneda+ Exponente+Referencia
     * @param Yameveo_Ceca_Model_Webservice_Response $response
     * @return boolean
     */
    /*public function checkResponseSignature(Pagantis_Pagantis_Model_Webservice_Response $response)
    {
        $txt = $this->_clave_encriptacion;
        $txt .= $response->getMerchantId();
        $txt .= $response->getAcquirerBin();
        $txt .= $response->getTerminalId();
        $txt .= $response->getNumOperacion();
        $txt .= $response->getImporte();
        $txt .= $response->getTipoMoneda();
        $txt .= $response->getExponente();
        $txt .= $response->getReferencia();

        // Calculate signature
        $signature = sha1($txt);

        // Compare received signature with calculated
        return strtolower($signature) === strtolower($response->getFirma());
    }*/
}
