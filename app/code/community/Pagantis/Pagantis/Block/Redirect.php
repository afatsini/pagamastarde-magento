<?php

/**
 * Pagantis_Pagantis payment form
 * 
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_Block_Redirect extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $helperPagantis = Mage::helper('pagantis_pagantis');
        $urlPagantis = $helperPagantis->getUrlPagantis();
        $paymentMethod = Mage::getModel('pagantis_pagantis/payment');
        $form = new Varien_Data_Form();


        $form->setAction($urlPagantis)
                ->setId('pagantis_pagantis_checkout')
                ->setLabel('Pagantis')
                ->setMethod('POST')
                ->setUseContainer(true);

        foreach ($paymentMethod->getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $form->addField('submitButton', 'submit', array('name' => 'submitButton', 'value' => 'Continue'));
        $this->setFormRedirect($form->toHtml());
    }

}