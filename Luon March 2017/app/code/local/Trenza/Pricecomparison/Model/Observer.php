<?php
class Trenza_Pricecomparison_Model_Observer
{
    static protected $_singletonFlag = false;
    public function savePricecomparison(Varien_Event_Observer $observer)
	{
        $table_name = "pricecomparison";
        $_write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
       
       //mail('faroque.golam@gmail.com' , 'luon test' , 'hello');
       
		if (!self::$_singletonFlag) {
			self::$_singletonFlag = true;
			
			$product = $observer->getEvent()->getProduct();            
            $_product_price = round($product->getPrice() , 2);
            $_cp_id = $product->getId();
		
			try {
				/**
				 * Perform any actions you want here
				 *
				 */
				$customFieldValue =  $this->_getRequest()->getPost('custom_field');
                $csv = array();
                
                if($_FILES['csv']['error'] == 0)
                {
                    $name = $_FILES['custom_field']['name'];
                    $ext = strtolower(end(explode('.', $_FILES['custom_field']['name'])));
                    $type = $_FILES['custom_field']['type'];
                    $tmpName = $_FILES['custom_field']['tmp_name'];
                
                
                    // check the file is a csv
                    if($ext === 'csv'){
                        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
                            // necessary if a large csv file
                            set_time_limit(0);
                
                            $row = 0;
                
                            $_sql = "DELETE FROM $table_name WHERE cp_id = " . $_write->quote($product->getId());
                            $_write->query($_sql);
                            
                            
                
                            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                                
                                if($row++ == 0)continue;
                                
                                
                                $_sql = "INSERT INTO $table_name set ";
                                
                                
                                $_comp_name = $data[0];
                                $_comp_price = $data[1];
                                $_mult = $data[2];
                                
                                //$_price_diff = $_comp_price - ($_product_price * $_mult);
                                
                                $_comp_name = iconv('ISO-8859-1', 'UTF-8', $_comp_name);
                                
                                $_sql .= " cp_id = " . $_write->quote($_cp_id);
                                $_sql .= " ,comp_name = " . $_write->quote($_comp_name);
                                $_sql .= ", comp_price = " . $_write->quote($_comp_price);
                                $_sql .= ", mult = " . $_write->quote($_mult);
                                
                                
                                //echo $_sql . '<br />';
                                
                                $_write->query($_sql);
                                
                                // inc the row
                                
                            }
                            fclose($handle);
                        }
                        
                        //echo '<pre>';
                        //print_r($csv);exit;
                        
                    }
                    
                    
                    
                    
                }

				/**
				 * Uncomment the line below to save the product
				 *
				 */
				//$product->save();
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
        
        
        
	}
 
	/**
	 * Retrieve the product model
	 *
	 * @return Mage_Catalog_Model_Product $product
	 */
	public function getProduct()
	{
		return Mage::registry('product');
	}
	
 /**
 * Shortcut to getRequest
 *
 */
 protected function _getRequest()
 {
 return Mage::app()->getRequest();
 }
}

?>