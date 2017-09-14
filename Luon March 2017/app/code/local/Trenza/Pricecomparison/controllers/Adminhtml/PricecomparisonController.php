<?php
class Trenza_Pricecomparison_Adminhtml_PricecomparisonController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
			$this->loadLayout()->_setActiveMenu("catalog/pricecomparison")->_addBreadcrumb(Mage::helper("adminhtml")->__("Price Comparison"),Mage::helper("adminhtml")->__("Price Comparison"));
			return $this;
	}
	public function indexAction() 
	{     
        $this->_initAction();        
        $this->renderLayout();
	}
    
    protected function _isAllowed()
    {
        return true;
    }        
    
    public function newAction() 
	{     
        $this->_forward('view');
	}
    
    public function viewAction() 
	{
        /*$id = $this->getRequest()->getParam('id');
        $log_data  = Mage::getModel('pricecomparison/pricecomparison')->load($id);
        
        Mage::register('log_data', $log_data);	*/
               
        $this->_initAction();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
	} 
    
    public function saveAction()
    {   
        /*$data = $this->getRequest()->getPost();
        $model = Mage::getModel('pricecomparison/pricecomparison');
        $model->setData($data)->setId($this->getRequest()->getParam('id'))->save();
        $message = "Save Successfully!";
        Mage::getSingleton('core/session')->addSuccess($message);*/
        #$this->_redirect('*/*/');
        
    
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
                if($ext === 'csv')
                {
                    if(($handle = fopen($tmpName, 'r')) !== FALSE) 
                    {  
                        ignore_user_abort(true);
            
                        $row = 0;            
                        $_sql = "TRUNCATE TABLE $table_name";
                        $_write->query($_sql);                        
            
                        while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) 
                        {
                            
                            if($row++ == 0)continue;
                            
                            $_sql = "INSERT INTO $table_name set ";
                            
                            if($product = Mage::getModel('catalog/product')->loadByAttribute('sku' , $data[0]) and (strlen($data[0]) > 2))
                            {
                                $_product_price = round($product->getFinalPrice() , 2);
                                $_cp_id = $product->getId();
                                $_product_name = $product->getName();
                                
                                $_comp_name = iconv('ISO-8859-1', 'UTF-8', $data[1]);
                                $_comp_price = $data[2];
                                $_mult = $data[3];
                                
                                $_price_diff = round($_comp_price - ($_product_price * $_mult) , 2);
                                $_price_diff2 = round(($_price_diff * 100) /$_comp_price , 0);
                               
                                
                                $_sql .= " cp_id = " . $_write->quote($_cp_id);
                                $_sql .= " ,sku = " . $_write->quote($data[0]);
                                $_sql .= " ,product_name = " . $_write->quote($_product_name);
                                $_sql .= " ,product_price = " . $_write->quote($_product_price);
                                $_sql .= " ,comp_name = " . $_write->quote($_comp_name);
                                $_sql .= ", comp_price = " . $_write->quote($_comp_price);
                                $_sql .= ", price_diff = " . $_write->quote($_price_diff2);                             
                                $_sql .= ", mult = " . $_write->quote($_mult);
                                
                                //echo $_sql . '<br />';//exit;
                                $_write->query($_sql);
                                
                            }
                            else
                            {
                                Mage::getSingleton('adminhtml/session')->addError("Product with the SKU ".$data[0]." is not exist!");
                            }
                        }
                        
                        //exit;
                        fclose($handle);
                        
                        $message = "Save Successfully!";
                        Mage::getSingleton('core/session')->addSuccess($message);
                        
                    }                        
                }
            }

		}
		catch (Exception $e) {			
            
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
    
        $this->_redirect('*/*/');
    
    }
    
     
                   
}