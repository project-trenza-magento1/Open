<?php


class Trenza_Inventorylog_Block_Adminhtml_Inventorylog extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

    	$this->_controller = "adminhtml_inventorylog";
    	$this->_blockGroup = "inventorylog";
    	$this->_headerText = Mage::helper("inventorylog")->__("Inventory Log");
        
    	parent::__construct();
	
    
        #$this->_removeButton('add');
    
	}

}