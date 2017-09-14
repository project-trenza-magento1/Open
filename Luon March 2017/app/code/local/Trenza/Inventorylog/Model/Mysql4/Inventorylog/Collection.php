<?php
    class Trenza_Inventorylog_Model_Mysql4_Inventorylog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
    {

		public function _construct()
        {
            
            parent::_construct();
            $this->_init("inventorylog/inventorylog");
		}

		

    }