<?php

/**
 * Enviroments for Pagantis_Pagantis config
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_Model_Source_Payment_Environment
{

    /**
     * Options list as array
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Retrieve all environments
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = array();

            $this->_options[] = array('label' => Mage::helper('pagantis_pagantis')->__('Real'), 'value' => Pagantis_Pagantis_Model_Webservice_Client::ENV_PRODUCTION);
            $this->_options[] = array('label' => Mage::helper('pagantis_pagantis')->__('Testing'), 'value' => Pagantis_Pagantis_Model_Webservice_Client::ENV_TESTING);
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
