<?php

class Trenza_Inventorylog_Block_Adminhtml_Inventorylog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
	{
			parent::__construct();
            
			$this->setId("inventorylogGrid");
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
       
        $collection = Mage::getModel("inventorylog/inventorylog")->getCollection();            
        $this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
            $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
			
            $this->addColumn("id", array(
				"header" => Mage::helper("inventorylog")->__("Id"),
				"index" => "id",
                'frame_callback' => array($this, 'grabItemLink'),
                
			));          
                        
            
            
           
           $this->addColumn("sku", array(
				"header" => Mage::helper("inventorylog")->__("Sku"),
				"index" => "sku",
				"width" => "300px",
			));
            
    
        /*$this->addColumn("order_number", array(
				"header" => Mage::helper("inventorylog")->__("Order Number"),
				"index" => "order_number",
				"width" => "300px",
			));
    
            
           $store = $this->_getStore();
            $this->addColumn('item_price',
                array(
                    'header'=> Mage::helper('inventorylog')->__('Price'),
                    'type'  => 'price',
                    'currency_code' => $store->getBaseCurrency()->getCode(),
                    'index' => 'item_price',
            ));
           
           
           $this->addColumn("serial_number", array(
				"header" => Mage::helper("inventorylog")->__("Serial Number"),
				"index" => "serial_number",
				"width" => "300px",
			)); 
            
                        
            $this->addColumn("model_number", array(
				"header" => Mage::helper("inventorylog")->__("Model Number"),
				"index" => "model_number",
				"width" => "300px",
			));
            
            
            $this->addColumn('order_time', array(
                'header'    => Mage::helper('inventorylog')->__('Order Time'),
                'index'     => 'order_time',
                'type'      => 'date',
                'input_format' => $dateFormatIso,
                'format'       => $dateFormatIso,
            ));
            
            
            $this->addColumn("comment", array(
				"header" => Mage::helper("inventorylog")->__("Comment"),
				"index" => "comment",
				"width" => "300px",
			));
            
            
            $this->addColumn("action_type", array(
				"header" => Mage::helper("inventorylog")->__("Action Type"),
				"index" => "action_type",
				"width" => "300px",
                'type'      => 'options',
                'options'   => array('1'=>'Sold' , '2'=>'Used for Recovery' , '3'=>'Exchange' , '4'=>'Lost' , '5'=>'Damaged' , '6'=>'Other'),
			));*/
	}
    
    public function grabItemLink($value, $row, $column, $isExport)
    {
        $id = (int)$row->getData('id');
        
        $item_link = $this->getUrl("*/adminhtml_inventorylog/view/id/".$id);    
        $html = '<a href="'.$item_link.'" title="Click to view details">'.$id.'</a>';
                
        
        return $html;
    }
    
	public function getRowUrl($row)
	{
        #return $this->getUrl("*/*/view", array("id" => $row->getId()));
        return false;
	}  

}
