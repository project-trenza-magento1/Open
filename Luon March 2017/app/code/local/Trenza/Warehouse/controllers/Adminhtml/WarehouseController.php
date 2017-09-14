<?php
class Trenza_Warehouse_Adminhtml_WarehouseController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction(){
		 $this->loadLayout()
		 ->_setActiveMenu('warehouse/items')
		 ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        return $this; 
		
		
	}   
    
    public function indexAction() {
		//$this->_title($this->__('Order'))->_title($this->__('Process Article'));
		//$this->_title($this->__('Sales'))->_title($this->__('Shipments'));
         $this->_initAction();
		 $this->renderLayout();
	   
    }
 
	public function processOrderAction(){		
		$arr_order_ids  = $this->getRequest()->getParam('order_ids');
		$datetime = new DateTime();
        
        $mycustommethod =array(
				'closestpostoffice'=>'IT16',
				'chooseapostoffice'=>'IT16',
                'smartpost'=>'ITSP',
                'matkahuolto'=>'MH80',                
				'homedelivery'=>'IT21',
		);
        
				
        $c = 0;
		if(count($arr_order_ids)>0)
        {
			foreach($arr_order_ids as $arr_order_id)
            {
				$order = Mage::getModel('sales/order')->load($arr_order_id);
                
                $shipping_method = $order->getShippingMethod();
                
                $shipping_method = str_replace('customshipping_' , '' , $shipping_method);
                
                $TransporterServiceCode = $mycustommethod[$shipping_method];
        
                
				if($order->getStatus()=='processing'){
					
					$_shipping_description = $order->getShippingDescription();
					                    
                    $OrderServicePointCode = $order->getCustomshippingcode();
                    
					$shippingAddress = $order->getShippingAddress();
					$billingAddress = $order->getBillingAddress();
					
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
                else
                {
                    Mage::getSingleton('adminhtml/session')->addWarning('Order only on processing state can be sent to ongoingsystems system.');
                }
				
			}

		}

		Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total of %d order(s) were successfully sent to ongoingsystems system.', $c
                        )
                    );
                    
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/"));
	}

	public function processArticleAction(){
		$arr_products  = $this->getRequest()->getParam('product');
		$c = 0;
		$art_param=array();
		if(count($arr_products)>0){
			
			foreach($arr_products as $pid) {
				$product = array();
				$product = Mage::getModel('catalog/product')->load($pid);

				if ($product->getTypeId() == 'simple') {

					$soapClientArticle = new SoapClient("https://api.ongoingsystems.se/boomerang/service.asmx?WSDL", array('soap_version' => SOAP_1_1, 'trace' => true));

					$art_param = array(
						'GoodsOwnerCode' => 'Luontaistukku - test',
						'UserName' => 'LuontaistukkuWSI',
						'Password' => '5gREJon4NgAb',
						'art' => array(
							'ArticleIdentification' => 'ProductCode',
							'ArticleOperation' => 'CreateOrUpdate',
							'ArticleSystemId' => $product->getId(),
							'ArticleNumber' => $product->getId(),
							'ArticleName' => $product->getName(),
							'ProductCode' => $product->getSku(),
							'BarCode' => $product->getSku(),
							'ArticleDescription' => $product->getShortDescription(),
							'ArticleUnitCode' => '',
							'Weight' => $product->getWeight(),
							'NetWeight' => $product->getWeight(),
							'Price' => $product->getPrice(),
							'PurchaseCurrencyCode' => Mage::app()->getStore()->getCurrentCurrencyCode(),
							'IsStockArticle' => true,
							'ArticleUnitCode' => 'Pcs',
							'ArticleStructureSpecification' =>array(
								'StructureArticleDefinition'=>array(
									'NumberOfItems'=>100,
									'ArticleStructureType'=>'StructureArticle',
								),

							),
							//'TypeId' => $product->getTypeId(),
						)
					);


					try {
						// Log
						$result = $soapClientArticle->__soapCall("ProcessArticle", array($art_param));	
                        
                                                
                        $c++;					
					} 
                    catch (SoapFault $fault) {
						//echo $fault->faultcode . '-' . $fault->faultstring;
                        Mage::getSingleton('adminhtml/session')->addError($fault->faultcode . '-' . $fault->faultstring);                        
					}
					unset($soapClientArticle);
				}
			}

		}
        
        Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Total of %d record(s) were successfully updated on ongoingwarehouse system', $c
                        )
                    );
		
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalog_product/"));

	}
	
	
	public function ongoingStatusAction(){
		$arr_order_ids = $this->getRequest()->getParam('order_ids');  //$_REQUEST['order_ids'];
		//$arr_order_ids  = explode(',',$order_ids);
		$ap_param=array();

		if(count($arr_order_ids)>0){
			foreach($arr_order_ids as $arr_order_id) {
				$ap_param=array();
				$order = array();
				$order = Mage::getModel('sales/order')->load($arr_order_id);
				//echo $order->getStatus(); exit;

				 if($order->getStatus()=='order_to_ongoing'){

					$soapClientArticle = new SoapClient("https://api.ongoingsystems.se/boomerang/service.asmx?WSDL", array('soap_version' => SOAP_1_1, 'trace' => true));

					$ap_param = array(
						'OrderId' => $order->getOngoing_orderid(),
						'UserName' => 'LuontaistukkuWSI',
						'Password' => '5gREJon4NgAb'
					);

					try 
                    {
						// Log
						$result = $soapClientArticle->__soapCall("GetOrder", array($ap_param));

						//echo 'Ongoing OrderId: '.$result->GetOrderResult->OrderInfo->OrderId.'<br>';
						$order_status_num =  $result->GetOrderResult->OrderInfo->OrderStatusNumber;
						$TransporterOrderNumber = $result->GetOrderResult->OrderInfo->TransporterOrderNumber;
						//echo 'Order Status Text: '.$result->GetOrderResult->OrderInfo->OrderStatusText.'<br>';
                        
                        $WayBill = $result->GetOrderResult->OrderInfo->WayBill;
                        
                        if(($WayBill != '' || $TransporterOrderNumber !='')  && ($order_status_num == 450 || $order_status_num == 500))
                        {
                            
                            $order = Mage::getModel('sales/order')->load($result->GetOrderResult->OrderInfo->GoodsOwnerOrderNumber);

                            $orderIncrementId = $order->getIncrementId();

							if($WayBill != ''){
								$shipmentTrackingNumber = $WayBill;
							}else{
								$shipmentTrackingNumber = $TransporterOrderNumber;
							}
    
                            //$customerEmailComments = 'ANY COMMENTS FOR THE ORDER RECIPIENT CUSTOMER';
    
                            if (!$order->getId()) {
                                Mage::throwException("Order does not exist, for the Shipment process to complete");
                            }
    
                            if ($order->canShip()) 
                            {
                                try 
                                {
                                    $shipment = Mage::getModel('sales/service_order', $order)
                                        ->prepareShipment($this->getItemQtys($order));

									$customerEmailComments = '';
                                    $arrTracking = array(
                                        'carrier_code' => $order->getShippingCarrier()->getCarrierCode(),
                                        'title' => $order->getShippingCarrier()->getConfigData('title'),
                                        'number' => $shipmentTrackingNumber,
                                    );
    
                                    $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
                                    $shipment->addTrack($track);
    
                                    // Register Shipment
                                    $shipment->register();
    
                                    // Save the Shipment
									if($this->saveShipment($shipment, $order, $customerEmailComments)){
										// Finally, Save the Order
										$this->saveOrder($order);
									}
    

                                } 
                                catch (Exception $e) 
                                {
                                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                                }
                            }
                            
                        }                        

					} 
                    catch (SoapFault $fault) 
                    {
						#echo $fault->faultcode . '-' . $fault->faultstring;
                        Mage::getSingleton('adminhtml/session')->addError($fault->faultcode . '-' . $fault->faultstring);
					}
					unset($soapClientArticle);
				}
			}

		}

		//$this->_initAction();
		//$this->renderLayout();
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/"));

	}

	public function synInventoryAction(){
		$_write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tablePrefix = (string) Mage::getConfig()->getTablePrefix();
		$soapClient = new SoapClient("https://api.ongoingsystems.se/boomerang/service.asmx?WSDL", array('soap_version' => SOAP_1_1, 'trace'   => true));

		$ap_param = array(
			'GoodsOwnerId' => '134',
			'UserName' => 'LuontaistukkuWSI',
			'Password' => '5gREJon4NgAb'
		);

		try {
			// Log
			$info = $soapClient->__soapCall("GetInventory", array($ap_param));

			$result = $info->GetInventoryResult->InventoryLines->InventoryLine;
			
			if(count($result)>0):
				foreach($result as $list):
						//$productCode = $list->Article->ProductCode;
						$articleNumber = $list->Article->ArticleNumber;
						$numberOfItems = $list->NumberOfItems;
					
					    //if($articleNumber==690):
						$query = "UPDATE {$tablePrefix}cataloginventory_stock_item SET qty='$numberOfItems'";
						if((int)($numberOfItems > 0)):
						$query .= ", is_in_stock=1";
						else:
							$query .= ", is_in_stock=0";
						endif;
						$query .= " WHERE product_id=".$articleNumber;
						$_write->query($query);
						//endif;

				endforeach;
				$process = Mage::getModel('index/indexer')->getProcessByCode('cataloginventory_stock');
				$process->reindexAll();
			endif;

		} catch (SoapFault $fault) {
			echo $fault->faultcode.'-'.$fault->faultstring;
		}
		unset($soapClient);
		
	    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                            'Stock Quantity has been successfully updated on your system'
                        )
        );
		Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("adminhtml/catalog_product/"));

	}

    
    private function getItemQtys(Mage_Sales_Model_Order $order){
        $qty = array();
    
        foreach ($order->getAllItems() as $_eachItem) {
            if ($_eachItem->getParentItemId()) {
                $qty[$_eachItem->getParentItemId()] = $_eachItem->getQtyOrdered();
            } else {
                $qty[$_eachItem->getId()] = $_eachItem->getQtyOrdered();
            }
        }
    
        return $qty;
    }
    
    
    private function saveShipment(Mage_Sales_Model_Order_Shipment $shipment, Mage_Sales_Model_Order $order, $customerEmailComments = ''){
        if($order->getStatus()=='order_to_ongoing'){
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($order)
                ->save();
    
            $emailSentStatus = $shipment->getData('email_sent');
            $shipment->sendEmail(true, $customerEmailComments);
            $shipment->setEmailSent(true);
    
            return true;
        }
    }
    
    private function saveOrder(Mage_Sales_Model_Order $order){
    
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        $order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
    
        $order->save();
    
        return true;
    }
    
      
}