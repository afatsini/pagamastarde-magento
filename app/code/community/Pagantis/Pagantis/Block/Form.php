<?php

/**
 * Pagantis_Pagantis payment form
 * 
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_Block_Form extends Mage_Payment_Block_Form
{

    protected function _construct()
    {
        $session = Mage::getSingleton('checkout/session');
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('pagantis/form.phtml');
        $this->setTemplate('pagantis/pagantis.phtml')
            ->setMethodLabelAfterHtml($mark->toHtml())
            ->setMethodTitle('Paga en cuotas')
        ;
        return parent::_construct();
    }

}
