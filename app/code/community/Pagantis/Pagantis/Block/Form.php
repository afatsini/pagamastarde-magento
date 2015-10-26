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

        $title="Financiación:";
        $session = Mage::getSingleton('checkout/session');
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setData('inst2',$this->instAmount(2));
        $mark->setData('inst3',$this->instAmount(3));
        $mark->setData('inst4',$this->instAmount(4));
        $mark->setData('inst5',$this->instAmount(5));
        $mark->setData('inst6',$this->instAmount(6));
        $mark->setTemplate('pagantis/form.phtml');
        $this->setTemplate('pagantis/pagantis.phtml')
            ->setMethodLabelAfterHtml($mark->toHtml())
            ->setMethodTitle($title)

        ;
        return parent::_construct();
    }

    /*
    * function instAmount
    * calculate the price of the installment
    * param $amount : amount in cents of the total loan
    * param $num_installments: number of installments, integer
    * return float with amount of the installment
    */
    protected function instAmount ($num_installments) {
      $quote = Mage::getModel('checkout/session')->getQuote();
      $quoteData= $quote->getData();
      $amount=$quoteData['grand_total']*100;
      $config = Mage::getStoreConfig('payment/pagantis');
      $discount = $config['discount'];
      if ( $discount ){
        $result= ($amount/100) / $num_installments;
      }else{
      $r = 0.25/365; #daily int
      $X = $amount/100; #total loan
      $aux = 1;  #first inst value
      for ($i=0; $i< $num_installments-2;$i++) {
        $aux = $aux + pow(1/(1+$r) ,(45+30*$i));
      }
    $result= (float)($X/$aux);
    }
    //add result to template
    return $result;
    }

}
