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


class Plumrocket_Invitations_Helper_Data extends Plumrocket_Invitations_Helper_Main
{
	const GUEST_KEY_PREFIX = 'g';

	private $_facebookApi = NULL;

	public function getInvitationsTopLinkUrl()
	{
		$promoPage = Mage::getModel('invitations/config')->getPromoPage();
		if ($promoPage['enabled'])
			return Mage::getUrl('invitations/promo');
		else
			return $this->getInvitationsLinkUrl();
	}
	
	public function getInvitationsLinkUrl()
	{
		return Mage::getUrl('invitations');
	}
	
	public function getUrlWithInviter($url = '', $data = array(), $inviterId = null)
	{
		if (is_null($inviterId)){
			$inviterId = $this->getCurrentCustomerId();
		}
		$data['inviter'] = $inviterId;
		return Mage::getUrl($url, $data);
	}

	public function addInviterToUrl($url, $inviterId = null)
	{
		if (is_null($inviterId)){
			$inviterId = $this->getCurrentCustomerId();
		}

		if ($inviterId) {
			$url .= ((strpos($url, '?') !== false) ? '&' : '?') . 'inviter=' . $inviterId;
		}

		return $url;
	}

	public function getFacebookApi()
	{
		if ($this->_facebookApi === NULL)
		{
			include 'FacebookSdk/facebook.php';
			$facebookABook = Mage::getModel('invitations/addressbooks')->getByKey('facebook');
			//var_dump($settings);
			$this->_facebookApi = new Facebook(array(
			   'appId' => $facebookABook->getSettingByKey('application_id'),
			   'secret' => $facebookABook->getSettingByKey('secret_key'),
			   //'cookie' => true,
			));
		}
		return $this->_facebookApi;
	}
	
	
	
	public function admCheckAccess($key)
	{
		return;
	}
	
	public function admResultSuccess($message){
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__($message));
		header('Location: '.getenv("HTTP_REFERER"));
		exit();
	}
	
	public function admResultError($message){
		Mage::getSingleton('adminhtml/session')->addError($this->__($message));
		header('Location: '.getenv("HTTP_REFERER"));
		exit();
	}
	
	public function getCurrentCustomer()
	{
		if (Mage::app()->isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) 
			return Mage::getSingleton('customer/session')->getCustomer();
		else
			return false;
	}
	
	public function getCurrentCustomerId()
	{
		if ($customer = $this->getCurrentCustomer())
			return $customer->getId();
		else
			return false;
	}
	
	public function getCustomerByEmail($email)
	{
		return Mage::getModel('customer/customer')
			->setWebsiteId(Mage::app()->getWebsite()->getId())
			->loadByEmail($email);
	}
	
	public function getRefferalLink($tag = false, $guestId = null, $customerId = null)
	{
		if (is_null($guestId)){
			$key = $customerId ? $customerId : $this->getCurrentCustomerId();
		} else {
			$key = self::GUEST_KEY_PREFIX.$guestId;
		}

		if (empty($this->_data['refferal_link'][$key])) {
            $cRefferal = Mage::getModel('invitations/config')->getReferralLink();
			if ($cRefferal{0} == '/'){
				$cRefferal = substr($cRefferal, 1);
			}
			
			$cRefferalL = strlen($cRefferal) - 1;
			if ($cRefferal{$cRefferalL} == '/'){
				$cRefferal = substr($cRefferal, 0, $cRefferalL);
			}
				
			$this->_data['refferal_link'][$key] = Mage::getUrl($cRefferal).$key;
        }
        $link = $this->_data['refferal_link'][$key];
        if ($tag){
			$link = '<a href="'.$link.'">'.$link.'</a>';
		}
		return $link;
	}
	
	public function getFilteredInviteeText($variables = null, $striptags = true)
	{
		$text = Mage::getModel('invitations/config')->getInviteeEmailText();
		return $this->_getFilteredText($text, $variables, $striptags);
	}
	
	public function getFilteredInviteeSubject($variables = null, $striptags = true)
	{
		$text = Mage::getModel('invitations/config')->getInviteeEmailSubject();
		return $this->_getFilteredText($text, $variables, $striptags);
	}
	
	protected function _getFilteredText($text, $variables = null, $striptags = true)
	{
		
		if (is_null($variables)){
			$variables = $this->_getVariablesForFilter();
		}
		foreach($variables as $key => $value){
			$text = str_replace('{{'.$key.'}}', $value, $text);
		}
		
		foreach(array('<br/>', '<br>', '<br />') as $v){
			$text = str_replace($v, "\n\r", $text);
		}
		
		if ($striptags){
			$text = strip_tags($text);
		}
		
		$text = trim($text);
		
		return $text;
	}
	
	protected function _getVariablesForFilter()
	{
		return array(
			'referral_link' => $this->getRefferalLink(),
			'customer_name' => (($this->getCurrentCustomer()) ? $this->getCurrentCustomer()->getName() : ''),
		);
	}
	
	
	
	public function moduleEnabled($storeId = null)
	{
		return Mage::getStoreConfig('invitations/general/enabled', $storeId);
	}
	
	public function modulePlumrocketRewardpointsEnabled()
	{
		if ((($module = Mage::getConfig()->getModuleConfig('Plumrocket_Rewards')) && ($module->is('active', 'true'))))
		{
			$result = Mage::getStoreConfig('rewards/general/enabled'); 
			return !empty($result);
		}
		return false;
	}
	
	public function checkAdminAccess($page)
	{
		$_session = Mage::getSingleton('admin/session');
		if ($_session->isLoggedIn() && !$_session->isAllowed('plumrocket/'.$page))
		{
			//Mage::getSingleton('adminhtml/session')->addError($this->__('Access denite.'));
			Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl('adminhtml/'));
		}
	}


	public function disableExtension()
    {
        $resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_write');
		$connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('invitations/general/enabled', 'invitations/promo_page/enabled'))));
        $config = Mage::getConfig();
        $config->reinit();
        Mage::app()->reinitStores();
    }


	public function curlFileGetContents($url)
	{
		$curl = curl_init();
		$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
		
		curl_setopt($curl,CURLOPT_URL,$url);	//The URL to fetch. This can also be set when initializing a session with curl_init().
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);	//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);	//The number of seconds to wait while trying to connect.	
		
		curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);	//The contents of the "User-Agent: " header to be used in a HTTP request.
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	//To follow any "Location: " header that the server sends as part of the HTTP header.
		curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);	//To automatically set the Referer: field in requests where it follows a Location: redirect.
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);	//The maximum number of seconds to allow cURL functions to execute.
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);	//To stop cURL from verifying the peer's certificate.
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		
		$contents = curl_exec($curl);
		curl_close($curl);
		return $contents;
	}

	public function isReferralPath()
	{
		$request = Mage::app()->getRequest();
		$referralLink = Mage::getModel('invitations/config')->getReferralLink();
		if ($referralLink{strlen($referralLink) -1} != '/'){
			$referralLink .= '/';
		}
        
		return (strpos($request->getPathInfo(), $referralLink) === 0);
	}


	public function getNewCouponCodeByRule($ruleId)
    {
    	if (!$ruleId) {
    		return false;
    	}

        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        if ( !$rule->getIsActive() || $rule->getCouponType()  == Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON) {
            $coupon = false;
        } else if($code = $rule->getCouponCode()) {
            $coupon = $code;
        } else {

            $generator = Mage::getModel('salesrule/coupon_massgenerator');

            $data = array(
                'max_probability'   => 25,
                'max_attempts'      => 10,
                'uses_per_customer' => 1,
                'uses_per_coupon'   => 1,
                'qty'               => 1, //number of coupons to generate
                'length'            => 8, //length of coupon string
                /**
                 * Possible values include:
                 * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC
                 * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL
                 * Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC
                 */
                'format'          => Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC,
                'rule_id'         => $rule->getId() //the id of the rule you will use as a template
            );

            $generator->validateData($data);
            $generator->setData($data);
            $generator->generatePool();


            $code = Mage::getResourceModel('salesrule/coupon_collection')
                ->addRuleToFilter($rule)
                ->addGeneratedCouponsFilter()
                ->getLastItem();

            $coupon = $code->getCode();
        }

        return $coupon;
    }

}
