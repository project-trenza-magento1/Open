<?php


class Trenza_Pricecomparison_Block_Adminhtml_Pricecomparison extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

    	$this->_controller = "adminhtml_pricecomparison";
    	$this->_blockGroup = "pricecomparison";
    	$this->_headerText = Mage::helper("pricecomparison")->__("Price Comparison");
        
    	parent::__construct();
	
    
        #$this->_removeButton('add');
    
	}

}