<?php
#mail('faroque.golam@gmail.com' , 'test' , 'just enter');

ob_start();  ini_set('max_execution_time', 0);  set_time_limit(0);    
require_once 'app/Mage.php';    

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);   
$_write = Mage::getSingleton('core/resource')->getConnection('core_write');
$_read = Mage::getSingleton('core/resource')->getConnection('core_read');

    /*$datetime = new DateTime();	
    echo 'DeliveryDate: ' . $datetime->format(DateTime::W3C);
    echo '<br />DeliveryDate: ' . date("Y-m-d");
    exit;*/



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
            
            
            /*$order_date = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
            
            echo $order_date;
            exit;*/
            
            
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
                
                
                ### Find if the order is a guest order or not
                if($order->getCustomerIsGuest())
                {
                    ### GUEST TO CUSTOMER CODE STARTS HERE  
                    
                    $email = $order->getCustomerEmail();
                    $customer = Mage::getModel("customer/customer");
                    $customer->setWebsiteId(1);
                    $customer->loadByEmail($email);                    
                    
                    if(!$customer->getId()) 
                    {
                		$first_name = $order->getCustomerFirstname();
                        $last_name = $order->getCustomerLastname();
                        $shipping_address = $order->getShippingAddress();
                        $telephone = $shipping_address->getTelephone();
                        $postalcode = $shipping_address->getPostcode();
                        $street = $shipping_address->getStreet();
                        $street = $street[0];
                        $city = $shipping_address->getCity();
                        $country = $shipping_address->getCountryId();
                        
                        $password = $customer->generatePassword(8);
                        mail('faroque.golam@gmail.com' , 'New Password' , $password);
                        
                        ### Create New Customer 
                		$newCustomer = array(
                						'email' => $email,
                						'store_id' => 1,
                						'website_id' => 1,
                						'company' => '',
                						'taxvat' => '',
                					);
                        try 
                        {
                        	
                            $customer->setData($newCustomer);
                            $customer->setPassword($password)
                                    ->setFirstname($first_name)
                                    ->setLastname($last_name);
                            $customer->save();
                            
                            $customer_id = $customer->getId();
                            
                            
                            ### Adding New Customer Address
                        	
                            try{
                                
                                $address = Mage::getModel("customer/address");
                            	$address->setCustomerId($customer_id)
                            		->setFirstname($first_name)
                            		->setLastname($last_name)
                            		->setCountryId($country)
                            		->setPostcode($postalcode)
                            		->setCity($city)
                            		->setTelephone($telephone)
                            		->setFax('')
                            		->setCompany('')
                            		->setStreet($street)
                            		->setIsDefaultBilling('1')
                            		->setIsDefaultShipping('1')
                            		->setSaveInAddressBook('1');
                                
                            	$address->save();
                            }
                            catch (Exception $e){
                            	
                                mail('faroque.golam@gmail.com' , 'Error occured : line 176 on processing_order.php' , $e->getMessage());
                            }
                            
                            //$customer->sendNewAccountEmail();
                        } 
                        catch (Exception $e) {
                             mail('faroque.golam@gmail.com' , 'Error occured : line 182 on processing_order.php' , $e->getMessage());
                        }
                    }
                    else
                    {
                        $customer_id = $customer->getId();
                    }                    
                    
                    
                    ### Customer and Order Mapping 
                    
                    
                    try{
                        $order->setCustomerId($customer_id);
                        $order->setCustomerGroupId(1);
                        $order->setCustomerIsGuest(0);
                        $order->save();
                    }
                    catch (Exception $e) 
                    {
                        mail('faroque.golam@gmail.com' , 'Error occured : line 200 on processing_order.php' , $e->getMessage());
                        
                    }    
                    
                    /* End Guest To customer Create  */                
                
                }
                
                #exit;                
                
                
                
                
                
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
        									//'DeliveryDate' => $datetime->format(DateTime::W3C),
                                            //'DeliveryDate' => $order->getCreatedAtStoreDate()->toString(Varien_Date::DATE_INTERNAL_FORMAT),
        									'DeliveryDate' => date("Y-m-d"),                                            
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
					
					$info = $soapClient->__soapCall("ProcessOrder", array($ap_param));

					$updateorder = Mage::getModel('sales/order')->load($info->ProcessOrderResult->GoodsOwnerOrderNumber);

					$state = 'order_to_ongoing';
					$status = 'order_to_ongoing';
                    
                    #$state = 'pending_payment';
					#$status = 'pending_payment';
                    
					$comment = '';
					$isCustomerNotified = false;

					$updateorder->setState($state, $status, $comment, $isCustomerNotified);

					$updateorder->setOngoing_orderid($info->ProcessOrderResult->OrderId);
                    //$updateorder->setStoreId(1);  
					$updateorder->save();
                    
                    
                    // Log
                    $this_dir = dirname(__FILE__);
                    $file_name = $this_dir . '/ongoinglog/' . $order->getIncrementId() . '.xml'; 
                    $myfile = fopen($file_name , "w");
                    fwrite($myfile , $soapClient->__getLastRequest());
                    fclose($myfile); 
                    
                    $c++;
                
                } 
                catch (SoapFault $fault) 
                { 
					echo $fault->faultcode.'-'.$fault->faultstring;
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



function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_numeric($key) ){
            $key = 'item'.$key; //dealing with <0/>..<n/> issues
        }
        if( is_array($value) ) {
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}


#mail('faroque.golam@gmail.com' , 'test' , 'just end');