<?php
 
class Trenza_Inventorylog_Block_Adminhtml_Inventorylog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
 
        $this->_objectId = 'id';
        $this->_blockGroup = 'inventorylog';
        $this->_controller = 'adminhtml_inventorylog';
        $this->_mode = 'edit';        
        
    }
 
 
    public function getBackUrl()
    {
        parent::getBackUrl();
        return $this->getUrl('*/*/');
    }
     
    public function getHeaderText()
    {
        
        return Mage::helper('inventorylog')->__('Log Details');
        
    }
    
    
 
}

