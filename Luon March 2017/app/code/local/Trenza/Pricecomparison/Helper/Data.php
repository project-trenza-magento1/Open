<?php
class Trenza_Pricecomparison_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    function priceCmpAll()
    {
        $table_name = "pricecomparison";
        $_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        #$_product_name = $_product->getName();
        #$_product_price = round($_product->getFinalPrice() , 2);
        
        
        //$_sql = "SELECT * , max( price_diff ) AS max_price_diff FROM $table_name GROUP BY cp_id";
        $_sql = "SELECT * FROM $table_name";        
        $_product_list = $_read->fetchAll($_sql);
        
        if($_product_list):
            return $_product_list;

        else:
            return false;
        endif;
    }
    
    function priceCmp()
    {
        $table_name = "pricecomparison";
        $_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $_product = Mage::registry('current_product');
        
        /*$_product_name = $_product->getName();
        $_product_price = round($_product->getFinalPrice() , 2);*/
        
        
        $_sql = "SELECT * FROM $table_name WHERE cp_id = " . $_product->getId();
        $_data_list = $_read->fetchAll($_sql);
        
        if($_data_list):
        
            return $_data_list;
        
            #$html = "<div class='data'><div class='filter'><div class='d-title first'>Vertailtu tuote</div><div class='d-title'>Tuotteen keskihinta</div><div class='d-title'>Luontaistukun vertailuhinta</div><div class='d-title'>Hintaero</div></div>";
        
            #foreach($_data_list as $_data):
            
                /*$_price_diff = round($_data['comp_price'] - ($_product_price * $_data['mult']) , 2);
                $_price_diff2 = round(($_price_diff * 100) /$_data['comp_price'], 0);*/
                           
                #$html .= "<div class='f-data'><div class='d-data first'>" . $_data['comp_name'] . "</div><div class='d-data'>" . Mage::helper('core')->currency($_data['comp_price'], true, false) . "</div><div class='d-data'>" . Mage::helper('core')->currency($_data['product_price'], true, false) . "</div><div class='d-data'>" . $_data['price_diff'] . "%</div></div>"; 
            #endforeach;
            
            #$html .= "</div>";
        
            return $html;
        else:
            return false;
        endif;
    }
    
    function maxPriceCmp(&$_product)
    {
        $table_name = "pricecomparison";
        $_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
                
        $_product_name = $_product->getName();
        $_product_price = round($_product->getFinalPrice() , 2);
        
        
        $_sql = "SELECT price_diff FROM $table_name WHERE cp_id = " . $_product->getId() . " ORDER BY price_diff DESC LIMIT 0,1";
        
        $_max_cmp_price = $_read->fetchOne($_sql);
        
        if($_max_cmp_price > 0)
            return $_max_cmp_price.'%';
        else
            return false;
    }
    
}
	 