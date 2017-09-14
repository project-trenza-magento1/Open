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


class Plumrocket_Rewards_Block_System_Config_Version extends Plumrocket_Base_Block_System_Config_Version
{
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$html = parent::render($element);

		$invitationsInstalled = (($module = Mage::getConfig()->getModuleConfig('Plumrocket_Invitations')) && ($module->is('active', 'true')));

		$html .= '<script type="text/javascript">
			function loadPjQuery_1_10_2(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "'.$this->getJsUrl('plumrocket/jquery-1.10.2.min.js').'";
				fjs.parentNode.insertBefore(js, fjs);
			}
			window.pjQuery_1_10_2 || loadPjQuery_1_10_2(document, "script", "loadPjQuery_1_10_2-lib")
		</script>';

		$html .= '<script type="text/javascript">
			plRewardsInterval = setInterval(function(){
				if (typeof pjQuery_1_10_2 == "undefined") return;
				clearInterval(plRewardsInterval);
				pjQuery_1_10_2(function(){

					//Invitations Points
					var $ = pjQuery_1_10_2;
					var ir = $("#rewards_invitations_credits_award_reason");
					ir.change(function(){
						irChange(this);
					})
					irChange(ir);

					function irChange(e) {
						if (parseInt($(e).val())) {
							$("#row_rewards_invitations_credits_max_days").hide();
							$("#row_rewards_invitations_credits_min_sub_total").hide();
						} else {
							$("#row_rewards_invitations_credits_max_days").show();
							$("#row_rewards_invitations_credits_min_sub_total").show();
						}
					}

					//registration points
					'.($invitationsInstalled ? '' : '$("#row_rewards_registration_credits_restriction").hide();' ).'
				});
			}, 100);
		</script>';

		return $html;
	}
}
