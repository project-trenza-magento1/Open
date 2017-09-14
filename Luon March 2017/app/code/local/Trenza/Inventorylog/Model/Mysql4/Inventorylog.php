<?php
class Trenza_Inventorylog_Model_Mysql4_Inventorylog extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {        
        $this->_init("inventorylog/inventorylog", "id");
    }
}