<?php
 
class Trenza_Customshipping_Model_Observer extends Varien_Object
{
    public function saveShippingMethod($evt)
    {
       
       
       $quote = $evt->getQuote();
       
       if(($quote->getCustomshippingcode() != Mage::getSingleton('core/session')->getCustomshippingcode()) and (strlen(Mage::getSingleton('core/session')->getCustomshippingcode()) > 0))
       {    
            $quote->setCustomshippingcode(Mage::getSingleton('core/session')->getCustomshippingcode());
       }
       
       /*$order = $evt->getOrder();
       
       mail('faroque.golam@gmail.com' , 'test' , $order->getId() . ' :: ' . Mage::getSingleton('core/session', array ('customshippingcode' => 'frontend')));
       
       
       $order->setCustomshippingcode(Mage::getSingleton('core/session')->getCustomshippingcode());
       
       $order->save();*/
    }
    
    public function saveShippingMethodOrder($evt)
    {
        $order = $evt->getOrder();
        $quote = $evt->getQuote();
        
        $shippingAddress = $quote->getShippingAddress();
        $methodName = $shippingAddress->getShippingMethod();
        
        $shippingDescription = $shippingAddress->getShippingDescription();
        $code = $quote->getCustomshippingcode();
        #mail('faroque.golam@gmail.com' , 'method name' , $methodName . ' :: ' . $code);
        
        
        if($methodName == 'customshipping_chooseapostoffice')
        {
            $shippingDescription .= ' ( ' . Mage::helper('customshipping')->postibycode($code) . ' )';
        }
        elseif($methodName == 'customshipping_smartpost')
        {
            $shippingDescription .= ' ( ' . Mage::helper('customshipping')->smartpostbycode($code) . ' )';
        }
        elseif($methodName == 'customshipping_matkahuolto')
        {
            $shippingDescription .= ' ( ' . Mage::helper('customshipping')->matkahuoltobycode($code) . ' )';
        }
        else
        {
            $shippingDescription .= ' ( No pickup point mentioned )';
        }
        
        
        $order->setShippingDescription($shippingDescription);
        $order->setCustomshippingcode($quote->getCustomshippingcode());
        $order->save();
    }
    
}