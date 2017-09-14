<?php

class Trenza_Customshipping_Model_All
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'customshipping';
    
    protected $_codeone = 'closestpostoffice';
    protected $_codetwo = 'chooseapostoffice';
    protected $_codethree = 'smartpost';
    protected $_codefour = 'matkahuolto';
    protected $_codefive = 'homedelivery';
    

    public function getFormBlock(){
        return 'customshipping/customshipping';
    }
 
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    $result = Mage::getModel('shipping/rate_result');
    
    
    $result->append($this->_HomeDeliveryRate($request));
    
    $result->append($this->_MatkahuoltoRate($request));
    $result->append($this->_SmartPostRate($request));
    
    
    
    $result->append($this->_getChooseAPostOfficeRate($request));
    
    /*disabled according to client's requirements'*/
    #$result->append($this->_getClosestPostOfficeRate($request));
    
    
    
    
    
 
    return $result;
  }
 
  public function getAllowedMethods()
  {
    /*return array(
      'default' => $this->getConfigData('closestpostoffice'),
      'chooseapostoffice' => $this->getConfigData('chooseapostoffice'),
      'smartpost' => $this->getConfigData('smartpost'),
      'matkahuolto' => $this->getConfigData('matkahuolto'),
      'homedelivery' => $this->getConfigData('homedelivery'),
    );*/
    
    
    
    return array(      
      'homedelivery' => $this->getConfigData('homedelivery'),
      
      'matkahuolto' => $this->getConfigData('matkahuolto'),
      'smartpost' => $this->getConfigData('smartpost'),
      
      
      
      'chooseapostoffice' => $this->getConfigData('chooseapostoffice'),      
      'default' => $this->getConfigData('closestpostoffice'),
      
    );
    
  }
 
    protected function _getClosestPostOfficeRate($request)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
         
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('closestpostoffice'));
        $rate->setMethod($this->_codeone);
        $rate->setMethodTitle($this->getConfigData('closestpostoffice'));
        
        if($request->getBaseSubtotalInclTax() > 49.99)
            $rate->setPrice(0);
        else
            $rate->setPrice($this->getConfigData('shippingcost'));
        
        
        $rate->setCost(0);
         
        return $rate;
    }
  
    protected function _getChooseAPostOfficeRate($request)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('chooseapostoffice'));
        $rate->setMethod($this->_codetwo);
        $rate->setMethodTitle($this->getConfigData('chooseapostoffice'));
        
        if($request->getBaseSubtotalInclTax() > 49.99)
            $rate->setPrice(0);
        else
            $rate->setPrice($this->getConfigData('shippingcost'));
        
        $rate->setCost(0);
        return $rate;
    }
    
       
    
    protected function _SmartPostRate($request)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('smartpost'));
        $rate->setMethod($this->_codethree);
        $rate->setMethodTitle($this->getConfigData('smartpost'));
        
        if($request->getBaseSubtotalInclTax() > 49.99)
            $rate->setPrice(0);
        else
            $rate->setPrice($this->getConfigData('shippingcost'));
        
        $rate->setCost(0);
        return $rate;
    }
    
    
    protected function _MatkahuoltoRate($request)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('matkahuolto'));
        $rate->setMethod($this->_codefour);
        $rate->setMethodTitle($this->getConfigData('matkahuolto'));
        
        if($request->getBaseSubtotalInclTax() > 49.99)
            $rate->setPrice(0);
        else
            $rate->setPrice($this->getConfigData('shippingcost'));
        
        
        $rate->setCost(0);
        return $rate;
    }
    
    protected function _HomeDeliveryRate($request)
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('homedelivery'));
        $rate->setMethod($this->_codefive);
        $rate->setMethodTitle($this->getConfigData('homedelivery'));
        
        
        $rate->setPrice(6.99);
        //$rate->setPrice($request->getBaseSubtotalInclTax());
        
        $rate->setCost(0);
        
        $rate->setSortOrder(100);
        
        return $rate;
    }
    

}