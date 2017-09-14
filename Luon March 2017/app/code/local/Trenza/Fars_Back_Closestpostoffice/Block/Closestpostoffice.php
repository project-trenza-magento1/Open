<?php
class Trenza_Closestpostoffice_Block_Closestpostoffice extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function __construct(){
        $this->setTemplate('checkout/posti.phtml');      
    }
}