<?php

class Trenza_Pricecomparison_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'edit_form';
        $this->_blockGroup = 'pricecomparison';
        $this->_controller = 'adminhtml_pricecomparison';
        
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('reset');
        
        
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        
        
    }

    public function getHeaderText()
    {
        
        return Mage::helper('pricecomparison')->__('Mass Update');
        
    }
}
