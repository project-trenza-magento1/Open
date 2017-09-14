<?php
class Trenza_Pricecomparison_Model_Mysql4_Pricecomparison extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {        
        $this->_init("pricecomparison/pricecomparison", "id");
    }
}