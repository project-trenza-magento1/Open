<?php
class Trenza_Customshipping_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		//$this->loadLayout();     
		//$this->renderLayout();
        
        $pickup_code = $this->getRequest()->getParam('customshippingcode',false);
        
        mail('faroque.golam@gmail.com' , 'custom shipping data save' , $pickup_code);
        
        //Mage::getSingleton('core/session')->setCustomshippingcode($pickup_code);
        
        
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        
        $quote->setCustomshippingcode($pickup_code)->save();
        
        //echo 'success';
    }
}