<?php

class Juhosyrj_KlarnaAutoInvoicer_Model_Observer
{
    public function salesOrderShipmentSaveAfter($event)
    {
		$shipment = $event->getShipment();
        $order = $shipment->getOrder();

		$klarna = Mage::getModel('klarna/klarna');
		
		try
		{
			if (Mage::helper('klarna')->isMethodKlarna($order->getPayment()->getMethod())) {
				$this->_createInvoice($order);
            } else {
				Mage::log('not Klarna order', null, 'Klarna_autoinvoicer.log');
            }			
        } catch (Exception $e) {
			Mage::log($e->getMessage(), null, 'Klarna_autoinvoicer.log');
		}

        return $this;
    }
  
 
    protected function _createInvoice($order)
    {
		Mage::log('_createAutoInvoice starting', null, 'Klarna_autoinvoicer.log');
        $result = array();

        $qtyData = array();
        $totalQty = 0;

        /** Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllItems() as $item) {
            $qty = $item->getQtyShipped() - $item->getQtyInvoiced();
            if ($qty < 0) {
                $qty = 0;
            }
            $qtyData[$item->getItemId()] = $qty;
            $totalQty += $qty;
        }

        if (!$totalQty) {
            Mage::throwException('Invoice cannot be created, nothing is shipped');
        }

        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtyData);

        if (!$invoice) {
            Mage::throwException('Failed to create invoice');
        }

        if (!$invoice->getTotalQty()) {
            Mage::throwException('Cannot create an invoice without products');
        }

        $invoice->register();
        $invoice->setEmailSent(true);
        $invoice->getOrder()->setCustomerNoteNotify(true);
        $invoice->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        try {
            $invoice->sendEmail();
        } catch (Exception $e) {
            $result[] = 'Unable to send the invoice email';
        }

        try {
            if ($invoice->canCapture()) {
                $invoice->capture();
                $invoice->getOrder()->setIsInProcess(true);

                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            }
        } catch (Exception $e) {
            $result[] = 'Error capturing invoice: ' . $e->getMessage();
        }
		
        return $result;
    }
 
}