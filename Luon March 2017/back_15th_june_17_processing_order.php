<?php
#mail('faroque.golam@gmail.com' , 'test' , 'just enter');

ob_start();  ini_set('max_execution_time', 0);  set_time_limit(0);    
require_once 'app/Mage.php';    

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);   
$_write = Mage::getSingleton('core/resource')->getConnection('core_write');
$_read = Mage::getSingleton('core/resource')->getConnection('core_read');

try {
	$orderCollection = Mage::getResourceModel('sales/order_collection');
	$orderCollection->addFieldToFilter('status', 'Processing')
					->getSelect()
					->limit(3);
					
	$datetime = new DateTime();	
	$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
    
    $mycustommethod =array(
				'closestpostoffice'=>'IT16',
				'chooseapostoffice'=>'IT16',
                'smartpost'=>'ITSP',
                'matkahuolto'=>'MH80',                
				'homedelivery'=>'IT21',
		);
	
	//for Smart Post
		$site = 'http://ohjelmat.posti.fi/pup/v1/pickuppoints?type=smartpost';
		$file_data = file_get_contents($site);
		if($file_data)
		$file_arr = @json_decode($file_data);
		if($file_arr)
		{
			foreach($file_arr as $data)
			{
				$xml_arr[trim($data->PublicName)] = $data->PupCode;
			}
	   }


	if(is_array($orderCollection->getItems()) && count($orderCollection->getItems())>0):
        foreach ($orderCollection->getItems() as $order_arr)
        //foreach($arr_order_ids as $arr_order_id)
        {
			//$order = Mage::getModel('sales/order')->load($arr_order_id);
            
            $orderModel = Mage::getModel('sales/order');
			$order =   $orderModel->load($order_arr['entity_id']);
            
            $shipping_method = $order->getShippingMethod();
            
            $shipping_method = str_replace('customshipping_' , '' , $shipping_method);
            
            $TransporterServiceCode = $mycustommethod[$shipping_method];
    
            
			if($order->getStatus()=='processing'){
				
				$_shipping_description = $order->getShippingDescription();
				                    
                $OrderServicePointCode = $order->getCustomshippingcode();
                
				$shippingAddress = $order->getShippingAddress();
				$billingAddress = $order->getBillingAddress();
	               
    			}
                else
                {
                    Mage::getSingleton('adminhtml/session')->addWarning('Order only on processing state can be sent to ongoingsystems system.');
                }
                
                
				$invoice = $order->getInvoiceCollection()
						->addAttributeToSort('created_at', 'DSC')
						->setPage(1, 1)
						->getFirstItem();

				$orderItems = $order->getItemsCollection();
				
				$CustomerOrderLine = array();
				
				foreach ($orderItems as $item) 
                {
					if($item->getProductType()=='simple' || $item->getParentItemId()!='')
                    {
						$CustomerOrderLine[] = array(
							'OrderLineIdentification' => 'ExternalOrderLineCode',
							'ExternalOrderLineCode' => $item->getId(),
							'ArticleIdentification' => 'ArticleNumber',
							'OrderLineSystemId' => $item->getOrderId(),
							'ArticleSystemId' => $item->getProductId(),
							'ArticleNumber' => $item->getProductId(),
							'ArticleName' => $item->getName(),
							'CustomerLinePrice' => $order->getGrandTotal(),
							'NumberOfItems' => intval($item->getQtyOrdered()),
							'DoPick' => true,
							'ProductCode' => $item->getSku(),
							'Discount' => $item->getBaseDiscountAmount(),
							//'TypeId' => $item->getProductType(),
							//'Parent' => $item->getParentItemId(),
						);
					}

				}
                
                /*
                GUEST TO CUSTOMER CODE GOES HERE                    
                */
                $order_id = $item->getOrderId();
                $order = Mage::getModel("sales/order")->load($item->getOrderId());
                $email=$order->getCustomerEmail();
                $Firstname=$order->getCustomerFirstname();
                $Lastname=$order->getCustomerLastname();
                $shipping_address = $order->getShippingAddress();
                $telephone = $shipping_address->getTelephone();
                $postalcode = $shipping_address->getPostcode();
                $street = $shipping_address->getStreet();
                $street = $street[0];
                $city = $shipping_address->getCity();
                $country = $shipping_address->getCountryId();
                $websiteId = Mage::app()->getWebsite()->getId();
                $store = Mage::app()->getStore();
                $customer = Mage::getModel("customer/customer")->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $customer->loadByEmail($email);
                $password = $customer->generatePassword(8);
                if(!$customer->getId()) {
    			$websiteId = Mage::app()->getWebsite()->getId();
    			$store = Mage::app()->getStore();
                /* Create New Customer */
    			$newCustomer = array(
    							'email' => $email,
    							'store_id' => 1,
    							'website_id' => 1,
    							'company' => '',
    							'taxvat' => '',
    						);
    			  try {
    				$customer = Mage::getModel('customer/customer');
    				$customer->setData($newCustomer);
                    $customer->setPassword($password)
                            ->setFirstname($Firstname)
                            ->setLastname($Lastname);
    				$customer->save();
                    
                    $customer_data = Mage::getModel('customer/customer')->setWebsiteId(1);
                        $customer_data->loadByEmail($email);
                    /* Add New Customer Address */
                        if($customer_data->getId()) {
                            $customer_id = $customer_data->getId();
                    		$address = Mage::getModel("customer/address");
                    		$address->setCustomerId($customer_data->getId())
                    			->setFirstname($Firstname)
                    			->setLastname($Lastname)
                    			->setCountryId($country)
                    			->setPostcode($postalcode)
                    			->setCity($acity)
                    			->setTelephone($telephone)
                    			->setFax('')
                    			->setCompany('')
                    			->setStreet($street)
                    			->setIsDefaultBilling('1')
                    			->setIsDefaultShipping('1')
                    			->setSaveInAddressBook('1');
                    	try{
                    		$address->save();
                    	}
                    	catch (Exception $e){
                    		Zend_Debug::dump($e->getMessage());
                    	}
                    }
                    $customer->sendNewAccountEmail();
    			  } catch (Exception $e) {
    			  }
                }
                
                /* Customer and Order Mapping  
                $_order = Mage::getModel('sales/order')->load($order_id);         
                $_order->setCustomerId($customer_id); 
                $_order->setCustomerIsGuest(0); 
                $_order->setCustomerGroupId('1');
                $_order->save();
                */
                /* Customer and Order Mapping  */
                
                $_order = Mage::getModel('sales/order')->load($order_id);         
                $_order->setCustomerId($customer_id); 
                $_order->setStore(1); 
                $_order->setWebsiteId(1); 
                $_order->setCustomerIsGuest(0); 
                $_order->setCustomerGroupId('1');
                try{
                    $_order->save();
                }
                catch (Exception $e) 
                {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    
                    $fp = fopen('data.txt', 'w+');
                    
                    fwrite($fr , $sku.'>>>'.$e->getMessage());
                    
                }    
                
                /* End Guest To customer Create  */
                
                
        		//Api code start here
                
        		$soapClient = new SoapClient("https://api.ongoingsystems.se/boomerang/service.asmx?WSDL", array('soap_version' => SOAP_1_1, 'trace'   => true));
        		$ap_param = array(
        					'GoodsOwnerCode' => 'Luontaistukku - test',
        					'UserName' => 'LuontaistukkuWSI',
        					'Password' => '5gREJon4NgAb',
        					'co' => array(
        							'OrderInfo' => array(
        									'OrderIdentification' => 'GoodsOwnerOrderNumber',
        									'OrderOperation' => 'CreateOrUpdate',
        									'OrderId' => $order->getId(),
        									'GoodsOwnerOrderNumber' => $order->getId(),
        									'GoodsOwnerOrderId' => $order->getIncrementId(),
        									'ReferenceNumber' => $order->getIncrementId(),
        									'DeliveryDate' => $datetime->format(DateTime::W3C),
        									'CustomerPrice' => $order->getGrandTotal(),
        									'Language' => strtoupper(substr(Mage::getStoreConfig('general/locale/code', $order->getStoreId()), 0, 2)),
        									'DeliveryInstruction' => $order->getShippingDescription(),
        									'InvoiceNumber' => $invoice->getIncrementId(),
											'OrderServicePointCode' => $OrderServicePointCode,
        							),
        							'GoodsOwner' => array(
        									'GoodsOwnerIdentification' => 'GoodsOwnerCode',
        									'GoodsOwnerCode' => 'Luontaistukku - test',
        									'GoodsOwnerId' => 134,
        							),
        							'Customer' => array(
        									'CustomerOperation' => 'CreateOrUpdate',
        									'CustomerIdentification' => 'CustomerNumber',
        									'Name' => $shippingAddress->getName(),
        									'Address' => $shippingAddress->getStreetFull(),
        									'PostCode' => $shippingAddress->getPostcode(),
        									'City' => $shippingAddress->getCity(),
        									'TelePhone' => $shippingAddress->getTelephone(),
        									'Email' => $shippingAddress->getEmail(),
        									'CountryCode' => $shippingAddress->getCountryID(),
        									'NotifyByEmail' => false,
        									'NotifyBySMS' => false,
        									'NotifyByTelephone' => false,
        									'IsVisible' => true,
        									'InvoiceAddress' => array(
        											'Name' => $billingAddress->getName(),
        											'Address' => $billingAddress->getStreetFull(),
        											'PostCode' => $billingAddress->getPostcode(),
        											'City' => $billingAddress->getCity(),
        											'CountryCode' => $billingAddress->getCountryID(),
        											'TelePhone' => $billingAddress->getTelephone(),
        											'Email' => $billingAddress->getEmail(),
        											'NotifyByEmail' => false,
        											'NotifyBySMS' => false,
        											'NotifyByTelephone' => false,
        											'IsVisible' => true,
        									),
        							),
        							'CustomerOrderLines' => array(
        									'CustomerOrderLine' => $CustomerOrderLine,
        							),
									
									'TransporterContract' => array(
										'TransporterContractIdentification' => 'ServiceCode',
										'TransporterContractOperation' => 'Find',
										'TransportPayment' => 'Prepaid',
										'TransporterServiceCode' => $TransporterServiceCode,
								),
        
        					),
        				);
                        
				try 
                {
					// Log

                    
                    
					$info = $soapClient->__soapCall("ProcessOrder", array($ap_param));

					$updateorder = Mage::getModel('sales/order')->load($info->ProcessOrderResult->GoodsOwnerOrderNumber);

					$state = 'order_to_ongoing';
					$status = 'order_to_ongoing';
					$comment = '';
					$isCustomerNotified = false;

					$updateorder->setState($state, $status, $comment, $isCustomerNotified);

					$updateorder->setOngoing_orderid($info->ProcessOrderResult->OrderId);
                    $updateorder->setStoreId(1);  
					$updateorder->save();
                    
                    $c++;
                
                } 
                catch (SoapFault $fault) 
                { 
					//echo $fault->faultcode.'-'.$fault->faultstring;
                    Mage::getSingleton('adminhtml/session')->addError($fault->faultcode.'-'.$fault->faultstring);
				}
				unset($soapClient); 
				unset($CustomerOrderLine);

	              //Api Code End  
			
		}
        
        
	endif;

} catch(Exception $e){
	 echo $e->getMessage();
}
#mail('faroque.golam@gmail.com' , 'test' , 'just end');