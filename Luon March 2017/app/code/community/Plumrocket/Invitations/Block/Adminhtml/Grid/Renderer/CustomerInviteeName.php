<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Invitations
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php

class Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_CustomerInviteeName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
		$rowData = $row->getData();
        if($customerId = $rowData['invitee_customer_id'])
		{
			
			if ($customer = Mage::getModel('customer/customer')->load($customerId))
				return $customer->getFirstname().' '.$customer->getLastname();
				
			return $this->__('DELETED');
        }
		else
			return $this->__('NOT CONFIRMED');
    }
}
