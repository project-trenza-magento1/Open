<?php

class Trenza_Pricecomparison_Block_Adminhtml_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
	{
			parent::__construct();
			$this->setId("pricecomparison");
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
	       
			
			$this->addColumn("id", array(
				"header" => Mage::helper("pricecomparison")->__("ID"),
				"index" => "id",
				"width" => "300px",
			));
            
           

            ob_start();

            

			return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
        //return $this->getUrl("*/*/view", array("id" => $row->getId()));
        return false;
	}  

}
