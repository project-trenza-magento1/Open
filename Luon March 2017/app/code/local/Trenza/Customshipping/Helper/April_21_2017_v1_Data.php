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
                //$_arr[$_option['pupcode']] = $_option['postcode'] . ' - ' . $_option['address'];
                $_arr[$_option['pupcode']] = $_option['publicname'];                
            endforeach;
        endif;

        $arr_size = sizeof($_arr);
        if($arr_size < 6)
        {
			$postcode_next = $postcode[2]+1;
			$postcode_prev = $postcode[2]-1;
		
			$postcode_next = $postcode[0].$postcode[1].$postcode_next;
			$postcode_prev = $postcode[0].$postcode[1].$postcode_prev;
            $bellow_postcode = $postcode_prev;
            
            $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$bellow_postcode."%' ORDER BY 'postcode' ASC";
        
            $_options = $_read->fetchAll($_options_sql); 

            if($_options):
                foreach($_options as $_option):
                    $_arr[$_option['pupcode']] = $_option['publicname'];
                endforeach;
            endif;

            $upper_postcode = $postcode_next;
            
            $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$upper_postcode."%' ORDER BY 'postcode' ASC";
        
            $_options = $_read->fetchAll($_options_sql); 
            if($_options):
                foreach($_options as $_option):
                    $_arr[$_option['pupcode']] = $_option['publicname'];
                endforeach;
            endif;
        }
		$_arr = array_slice($_arr, 0, 15, true); // note "true" parameter for reserve key!
        return $_arr;
    }
    public function smartpostbycode($code)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_smartpost');
        
        $_data_sql = "SELECT publicname FROM $tableName WHERE pupcode = '" . $code . "'";
        
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
                $_arr[$_option['pupcode']] = $_option['publicname'];
            endforeach;
        endif;
        
        
        $arr_size = sizeof($_arr);
        
        if($arr_size < 6)
        {
			$postcode_next = $postcode[2]+1;
			$postcode_prev = $postcode[2]-1;
		
			$postcode_next = $postcode[0].$postcode[1].$postcode_next;
			$postcode_prev = $postcode[0].$postcode[1].$postcode_prev;
            $bellow_postcode = $postcode_prev;
            
            $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$bellow_postcode."%' ORDER BY 'postcode' ASC";
        
            $_options = $_read->fetchAll($_options_sql); 
            if($_options):
                foreach($_options as $_option):
                    $_arr[$_option['pupcode']] = $_option['publicname'];
                endforeach;
            endif;
            
            
            $upper_postcode = $postcode_next;
            
            $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$upper_postcode."%' ORDER BY 'postcode' ASC";
        
            $_options = $_read->fetchAll($_options_sql); 
            if($_options):
                foreach($_options as $_option):
                    $_arr[$_option['pupcode']] = $_option['publicname'];
                endforeach;
            endif;
        }
        $_arr = array_slice($_arr, 0, 15, true); // note "true" parameter for reserve key!
        return $_arr;
    }
    public function postibycode($code)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_posti');
        
        $_data_sql = "SELECT publicname FROM $tableName WHERE pupcode = '" . $code . "'";
        
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
                //$_arr[$_option['agentto']] = $_option['postcode'] . ' - ' . $_option['street'] . ' ' . $_option['city'];
                //$_arr[$_option['agentto']] = $_option['shipname'] . ', ' . $_option['street'] . ', ' . $_option['postcode']. ' ' . $_option['city'];
                $_arr[$_option['ncode']] = $_option['shipname'] . ', ' . $_option['postcode'];
            endforeach;
        endif;
        $arr_size = sizeof($_arr);
        
        if($arr_size < 6)
        {
			$postcode_next = $postcode[2]+1;
			$postcode_prev = $postcode[2]-1;
		
			$postcode_next = $postcode[0].$postcode[1].$postcode_next;
			$postcode_prev = $postcode[0].$postcode[1].$postcode_prev;
            $bellow_postcode = $postcode_prev;
            
            $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$bellow_postcode."%' ORDER BY 'postcode' ASC";
			//exit();
            $_options = $_read->fetchAll($_options_sql); 
            if($_options):
                foreach($_options as $_option):
                    //$_arr[$_option['agentto']] = $_option['shipname'] . ', ' . $_option['street'] . ', ' . $_option['postcode']. ' ' . $_option['city'];
                
                    $_arr[$_option['ncode']] = $_option['shipname'] . ', ' . $_option['postcode'];
                endforeach;
            endif;
            
            
            $upper_postcode = $postcode_next;
            
            $_options_sql = "SELECT * FROM $tableName WHERE postcode LIKE '".$upper_postcode."%' ORDER BY 'postcode' ASC";
        
            $_options = $_read->fetchAll($_options_sql); 
            if($_options):
                foreach($_options as $_option):
                    //$_arr[$_option['agentto']] = $_option['shipname'] . ', ' . $_option['street'] . ', ' . $_option['postcode']. ' ' . $_option['city'];
                
                    $_arr[$_option['ncode']] = $_option['shipname'] . ', ' . $_option['postcode'];
                endforeach;
            endif;
        }

		$_arr = array_slice($_arr, 0, 15, true); // note "true" parameter for reserve key!
        return $_arr;
    }
    public function matkahuoltobycode($code)
    {
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName =  Mage::getSingleton('core/resource')->getTableName('customshipping_matkahuolto');
        
        $_data_sql = "SELECT * FROM $tableName WHERE ncode = '" . $code . "'";
        
        $_data = $_read->fetchAll($_data_sql);
        
        //$_data_text = $_data[0]['street'] . ' ' . $_data[0]['city'];
        //$_data_text = $_data[0]['shipname'] . ', ' . $_data[0]['street'] . ', ' . $_data[0]['postcode']. ' ' . $_data[0]['city'];
        $_data_text = $_data[0]['shipname'];
        return $_data_text;
    }
    
        
}