<?php

class Trenza_Warehouse_Model_Warehouse extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('warehouse/warehouse');
    }
}
