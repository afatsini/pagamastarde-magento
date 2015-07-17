<?php

/**
 * Currencies for Pagantis_Pagantis config
 * 
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_Model_Source_Payment_Currency
{

    /**
     * Options list as array
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Retrieve all currencies
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();

            $this->_options[] = array('label' => Mage::helper('pagantis_pagantis')->__('Euro'), 'value' => Pagantis_Pagantis_Model_Currency::EUR);
            $this->_options[] = array('label' => Mage::helper('pagantis_pagantis')->__('Pound'), 'value' => Pagantis_Pagantis_Model_Currency::GBP);
            $this->_options[] = array('label' => Mage::helper('pagantis_pagantis')->__('US Dollar'), 'value' => Pagantis_Pagantis_Model_Currency::USD);
        }

        return $this->_options;
    }

    /**
     * To option array conversion
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

}
