<?php
class Trenza_Customshipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    public function smartpost($postcode)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_smartpost');
        
        if(strlen($postcode) > 3)$postcode = substr($postcode , 0 , 3);
        
        $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$postcode."%' ORDER BY 'postcode' ASC";
        
        $_options = $_read->fetchAll($_options_sql);
        
        $_arr = array();
        
        if($_options):
            foreach($_options as $_option):
                $_arr[$_option['pupcode']] = $_option['postcode'] . ' - ' . $_option['address'];
            endforeach;
        endif;
        
        return $_arr;
    }
    public function smartpostbycode($code)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_smartpost');
        
        $_data_sql = "SELECT address FROM $tableName WHERE pupcode = '" . $code . "'";
        
        $_data = $_read->fetchOne($_data_sql);
        
        return $_data;
    }
    
    
    public function posti($postcode)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_posti');
        
        if(strlen($postcode) > 3)$postcode = substr($postcode , 0 , 3);
        
        $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$postcode."%' ORDER BY 'postcode' ASC";
        
        $_options = $_read->fetchAll($_options_sql);
        
        $_arr = array();
        
        if($_options):
            foreach($_options as $_option):
                $_arr[$_option['pupcode']] = $_option['postcode'] . ' - ' . $_option['address'];
            endforeach;
        endif;
        
        return $_arr;
    }
    public function postibycode($code)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_posti');
        
        $_data_sql = "SELECT address FROM $tableName WHERE pupcode = '" . $code . "'";
        
        $_data = $_read->fetchOne($_data_sql);
        
        return $_data;
    }
    
    
    
    public function matkahuolto($postcode)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_matkahuolto');
        
        if(strlen($postcode) > 3)$postcode = substr($postcode , 0 , 3);
        
        $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$postcode."%' ORDER BY 'postcode' ASC";
        
        $_options = $_read->fetchAll($_options_sql);
        
        
        $_arr = array();
        
        if($_options):
            foreach($_options as $_option):
                $_arr[$_option['agentto']] = $_option['postcode'] . ' - ' . $_option['street'] . ' ' . $_option['city'];
            endforeach;
        endif;
        
        return $_arr;
    }
    public function matkahuoltobycode($code)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_matkahuolto');
        
        $_data_sql = "SELECT street,city FROM $tableName WHERE agentto = '" . $code . "'";
        
        $_data = $_read->fetchAll($_data_sql);
        
        $_data_text = $_data[0]['street'] . ' ' . $_data[0]['city'];
        
        return $_data_text;
    }
    
    
}