<?php
// app/code/local/Envato/Customshippingmethod/Model
class Trenza_Closestpostoffice_Model_Posti
extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'closestpostoffice';
    
    protected $_codeone = 'chooseapostoffice';
    protected $_codetwo = 'chooseapostoffice';
    protected $_codethree = 'smartpost';
    protected $_codefour = 'matkahuolto';
    protected $_codefive = 'homedelivery';
    

    public function getFormBlock(){
        return 'closestpostoffice/closestpostoffice';
    }
 
  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    $result = Mage::getModel('shipping/rate_result');
    
    $result->append($this->_getClosestPostOfficeRate());
    $result->append($this->_getChooseAPostOfficeRate());
    $result->append($this->_SmartPostRate());
    $result->append($this->_MatkahuoltoRate());
    $result->append($this->_HomeDeliveryRate());
 
    return $result;
  }
 
  public function getAllowedMethods()
  {
    return array(
      'default' => $this->getConfigData('closestpostoffice'),
      'chooseapostoffice' => $this->getConfigData('chooseapostoffice'),
      'smartpost' => $this->getConfigData('smartpost'),
      'matkahuolto' => $this->getConfigData('matkahuolto'),
      'homedelivery' => $this->getConfigData('homedelivery'),
    );
  }
 
    protected function _getClosestPostOfficeRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
         
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('closestpostoffice'));
        $rate->setMethod($this->_codeone);
        $rate->setMethodTitle($this->getConfigData('closestpostoffice'));
        $rate->setPrice($this->getConfigData('shippingcost'));
        $rate->setCost(0);
         
        return $rate;
    }
  
    protected function _getChooseAPostOfficeRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('chooseapostoffice'));
        $rate->setMethod($this->_codetwo);
        $rate->setMethodTitle($this->getConfigData('chooseapostoffice'));
        $rate->setPrice($this->getConfigData('shippingcost'));
        $rate->setCost(0);
        return $rate;
    }
    
       
    
    protected function _SmartPostRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('smartpost'));
        $rate->setMethod($this->_codethree);
        $rate->setMethodTitle($this->getConfigData('smartpost'));
        $rate->setPrice($this->getConfigData('shippingcost'));
        $rate->setCost(0);
        return $rate;
    }
    
    
    protected function _MatkahuoltoRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('matkahuolto'));
        $rate->setMethod($this->_codefour);
        $rate->setMethodTitle($this->getConfigData('matkahuolto'));
        $rate->setPrice($this->getConfigData('shippingcost'));
        $rate->setCost(0);
        return $rate;
    }
    
    protected function _HomeDeliveryRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('homedelivery'));
        $rate->setMethod($this->_codefive);
        $rate->setMethodTitle($this->getConfigData('homedelivery'));
        $rate->setPrice(0);
        $rate->setCost(0);
        return $rate;
    }
    

}