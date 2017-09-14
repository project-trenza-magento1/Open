<?php
 
class Trenza_Pricecomparison_Block_Adminhtml_Pricecomparison_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
 
        $this->_objectId = 'id';
        $this->_blockGroup = 'pricecomparison';
        $this->_controller = 'adminhtml_pricecomparison';
        $this->_mode = 'edit';        
        
    }
 
 
    public function getBackUrl()
    {
        parent::getBackUrl();
        return $this->getUrl('*/*/');
    }
     
    public function getHeaderText()
    {
        
        return Mage::helper('pricecomparison')->__('Price Comparison');
        
    }
    
    
 
}

