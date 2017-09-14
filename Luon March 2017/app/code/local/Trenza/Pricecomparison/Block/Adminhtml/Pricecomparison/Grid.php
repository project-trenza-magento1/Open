<?php

class Trenza_Pricecomparison_Block_Adminhtml_Pricecomparison_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
	{
			parent::__construct();
            
			$this->setId("pricecomparisonGrid");
			$this->setDefaultSort("id");
			$this->setDefaultDir("DESC");
			$this->setSaveParametersInSession(true);
            
	}
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

	protected function _prepareCollection()
	{	   
       
        $collection = Mage::getModel("pricecomparison/pricecomparison")->getCollection();            
        $this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
            $store = $this->_getStore();
			
            $this->addColumn("cp_id", array(
				"header" => Mage::helper("pricecomparison")->__("Product Id"),
				"index" => "cp_id",
                'frame_callback' => array($this, 'grabItemLink'),                
			));          
            
           $this->addColumn("sku", array(
				"header" => Mage::helper("pricecomparison")->__("Sku"),
				"index" => "sku",				
			));


            $this->addColumn("comp_name", array(
				"header" => Mage::helper("pricecomparison")->__("Comp. Product Name"),
				"index" => "comp_name",
				
			));
            
            $this->addColumn("comp_price", array(
				"header" => Mage::helper("pricecomparison")->__("Comp. Product Price"),
				"index" => "comp_price",				
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
			));
            
            $this->addColumn("mult", array(
				"header" => Mage::helper("pricecomparison")->__("Mult."),
				"index" => "mult",
				
			));
            
            $this->addColumn("price_diff", array(
				"header" => Mage::helper("pricecomparison")->__("Price Diff."),
				
                'frame_callback' => array($this, 'grabPriceDiff'),                
			));
            
            
	}
    
    public function grabItemLink($value, $row, $column, $isExport)
    {               
        
        $product_id = (int)$row->getData('cp_id');
        $product_link = $this->getUrl("adminhtml/catalog_product/edit/id/".$product_id);
    
        $html = '<a href="'.$product_link.'" target="_blank">'.$product_id.'</a>';
        return $html;
            
    }
    
    public function grabPriceDiff($value, $row, $column, $isExport)
    {               
        $comp_price= $row->getData('comp_price');
        $mult= $row->getData('mult');
        
        $product_id = (int)$row->getData('cp_id');
        $_product = Mage::getModel('catalog/product')->load($product_id);
        $_product_price = round($_product->getFinalPrice() , 2);
        $_price_diff = round($comp_price - ($_product_price * $mult) , 2);
        
        $html = $_price_diff;
        
        return $html;
            
    }
    
	public function getRowUrl($row)
	{
        #return $this->getUrl("*/*/view", array("id" => $row->getId()));
        return false;
	}  

}
