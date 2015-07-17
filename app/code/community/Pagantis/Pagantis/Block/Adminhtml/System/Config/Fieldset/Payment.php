<?php

/**
 * Pagantis_Pagantis payment form
 *
 * @package    Pagantis_Pagantis
 * @copyright  Copyright (c) 2015 Yameveo (http://www.yameveo.com)
 * @author	   Yameveo <yameveo@yameveo.com>
 * @link	   http://www.yameveo.com
 */
class Pagantis_Pagantis_Block_Adminhtml_System_Config_Fieldset_Payment
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
            return '<div class="adminLogoPagantis"></div>';
    }


}