<?php

class Trenza_Warehouse_Model_Mysql4_Warehouse extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the slider_id refers to the key field in your database table.
        $this->_init('warehouse/warehouse', 'id');
		//$this->_isPkAutoIncrement = false;
    }
	

}
