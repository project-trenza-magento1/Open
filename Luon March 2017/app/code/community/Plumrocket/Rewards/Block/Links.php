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
 * @package     Plumrocket_Reward_Points
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php  
  
class Plumrocket_Rewards_Block_Links extends Mage_Core_Block_Template
{   
	public function addRewardsTopLink($path, $label, $position = 91)
	{
		if ($parentBlock = $this->getParentBlock())
		{
			if ($gbc = Mage::getModel('rewards/points')->getByCustomer()){
				$count = $gbc->getAvailable();
			}
			
			if (empty($count)){
				$count = 0;
			}
			
			$text = $this->__($label, $count);
			$parentBlock->addLink($text, $path, $text, true, array(), $position, null, 'class="top-link-rewards-poits"');
		}
        return $this;
    }
}
