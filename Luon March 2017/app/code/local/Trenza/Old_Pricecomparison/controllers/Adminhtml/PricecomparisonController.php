<?php
class Trenza_Pricecomparison_Adminhtml_PricecomparisonController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				//$this->loadLayout()->_setActiveMenu("catalog/pricecomparison")->_addBreadcrumb(Mage::helper("adminhtml")->__("Price Comparison"),Mage::helper("adminhtml")->__("Price Comparison"));
			//	return $this;
		}
		public function indexAction() 
		{
		  
          try {
                       
             
			$customFieldValue =  $this->getRequest()->getPost('csvfile');
            
            $csv = array();
            
            
             
            if($_FILES['csvfile']['error'] == 0)
            {
                $table_name = "pricecomparison";
                $_write = Mage::getSingleton('core/resource')->getConnection('core_write');
                $_read = Mage::getSingleton('core/resource')->getConnection('core_read');
                
                
                $name = $_FILES['csvfile']['name'];
                $ext = strtolower(end(explode('.', $_FILES['csvfile']['name'])));
                $type = $_FILES['csvfile']['type'];
                $tmpName = $_FILES['csvfile']['tmp_name'];
                
                
            
                // check the file is a csv
                if($ext === 'csv'){
                    if(($handle = fopen($tmpName, 'r')) !== FALSE) 
                    {  
                       
                        // necessary if a large csv file
                        set_time_limit(0);
            
                        $row = 0;
            
                        $_sql = "TRUNCATE TABLE $table_name";
                        //$_write->query($_sql);
                        
                         
            
                        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                            
                            if($row++ == 0)continue;
                            
                            
                            $_sql = "INSERT INTO $table_name set ";
                            
                            //echo 'sku: '.$data[0] ;
                            
                            $product = Mage::getModel('catalog/product')->loadByAttribute('sku' , $data[0]);          
                            $_product_price = round($product->getPrice() , 2);
                            $_cp_id = $product->getId();
                            
                            $_comp_name = $data[1];
                            $_comp_price = $data[2];
                            $_mult = $data[3];
                            
                            //$_price_diff = $_comp_price - ($_product_price * $_mult);
                            
                            $_sql .= " cp_id = " . $_write->quote($_cp_id);
                            $_sql .= " ,comp_name = " . $_write->quote($_comp_name);
                            $_sql .= ", comp_price = " . $_write->quote($_comp_price);
                            $_sql .= ", mult = " . $_write->quote($_mult);
                            
                            
                            echo $_sql . '<br />';
                            
                            
                            //$_write->query($_sql);
                            
                            // inc the row
                            
                        }
                        exit;
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
			
            echo $e->getMessage();
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
          
          
          
			    
                $this->loadLayout();
                $this->_title($this->__("Price Comparison"));
                                
				
				
                
                $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
                
                //$this->_addContent($this->getLayout()->createBlock('pricecomparison/adminhtml_edit'));
                $this->_addContent($this->getLayout()->createBlock('pricecomparison/adminhtml_edit_form'));
                
                $this->_addContent($this->getLayout()->createBlock('pricecomparison/adminhtml_grid'));
                
                //$this->_addContent($this->getLayout()->createBlock('core/template')->setTemplate('pricecomparison/catalog/product/tab.phtml'));
                
                
                //$this->setTemplate('pricecomparison/catalog/product/tab.phtml');
                
                //$this->_addContent($this->getLayout()->createBlock("listingwizard/adminhtml_scanimage_picktemplate"));    		
                //$this->_addContent($this->getLayout()->createBlock("listingwizard/adminhtml_scanimage_picktemplate_form"));
                
                
                
                $this->renderLayout();
		}
		
        
        
}