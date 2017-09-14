<?php
class Trenza_Inventorylog_Model_Observer
{
    
    
    public function updateInventoryLogAfterOrderPlace($event)
    {        
        #$fp = fopen('datam.txt', 'w');        
        $order = $event->getOrder();
        
        $order_number = $order->getId();
        #$order_time = date('m/d/Y');
        #$order_time = date('Y-m-d H:i:s');
        
        $order_time = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        
        
        $action_type = 1;
                
        $data['order_number'] = $order_number;
        $data['order_time'] = $order_time;
        $data['action_type'] = $action_type;
        
        
        foreach ($order->getAllVisibleItems() as $item)
        {           
            $product_id = $item->getProductId();
            $product_model = Mage::getModel('catalog/product');
            $product = $product_model->load($product_id);
            
            $sku = $product->getSku(); 
            $model_number = $product->getmodelnumber();
            $price = $item->getPrice();
            
            $data['sku'] = $sku;
            $data['model_number'] = $model_number;
            $data['item_price'] = $price;
            #fwrite($fp , $data['sku'] . '=>' . $data['model_number']);
            
            try{
                #mail('faroque.golam@gmail.com' , 'log test start' , $data['sku']);
                Mage::getModel('inventorylog/inventorylog')->addData($data)->save();
                #mail('faroque.golam@gmail.com' , 'log test end' , $data['model_number']);   
            }
            catch (Exception $e) {
				#Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                mail('faroque.golam@gmail.com' , 'log test error' , $e->getMessage());			 
            }
                 
        }
        
        #fclose($fp);
    }
}

?>