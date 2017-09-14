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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Invitations_Block_System_Config_Rewardpoint  extends Mage_Adminhtml_Block_System_Config_Form_Field
{

	const REWARD_POINT_ROW_ID = "row_advancedrar_reward_point";

	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		if (!$this->isRewardPointEnabled()) {
			$message = Mage::helper('invitations')->__('Hint: Your customers can earn reward points for each invited friend. Get 10% discount on <a href="https://store.plumrocket.com/magento-extensions/reward-points-magento-extension.html" target="_blank">Reward Points Extension</a> with promo code: <strong>ARSAVE10</strong>');
		} elseif ($this->isRewardsForInvitations()) {
			$message = Mage::helper('invitations')->__('Please note, that customer is currently earns reward points for each invitee. If you don\'t want to award customers with both coupon codes and rewards - please <a href="'.$this->getUrl('*/*/*', array('section' => 'rewards')).'#rewards_invitations_credits-head" target="_blank">click here</a> to disable reward points for invitations.');
		} else {
			$message = null;
		}

		if ($message) {
			$html = $this->getCloseJs();
			$html .= '<tr id="' . self::REWARD_POINT_ROW_ID . '">';
			$html .= '<td colspan="4"><div style="' . $this->getMessageCss() . '">'. $message.'</div></td><tr>';
			return $html;
		}
	}


	public function isRewardPointEnabled()
	{
		return Mage::getConfig()->getModuleConfig('Plumrocket_Rewards')
			|| Mage::getSingleton('core/cookie')->get(self::REWARD_POINT_ROW_ID);
	}

	public function isRewardsForInvitations()
	{
		return Mage::getStoreConfig('rewards/invitations_credits/credits')
			&& Mage::getStoreConfig('rewards/general/enabled');
	}

	public function getMessageCss()
	{
		return "padding: 10px; padding-right: 15px; background-color: #E4F2FF; border: 1px solid #A9C9E7; margin-bottom: 7px; width: 570px; position: relative;";
	}

}