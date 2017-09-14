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

class Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	protected $_field = NULL;
	
	public function setField($value)
	{
		$this->_field = $value;
		return $this;
	}
	
    public function render(Varien_Object $row)
    {
		$rowData = $row->getData();
		$date = $rowData[$this->_field];
        if($date == '0000-00-00 00:00:00')
		{
			return '';
        }
		else
			return date('M d, Y', strtotime($date));
        
		
    }
}
