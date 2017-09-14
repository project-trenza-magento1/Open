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


class Plumrocket_Invitations_InviterController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();

		$_helper = Mage::helper('invitations');
		if (!($_helper->moduleEnabled()))
			$this->_toBaseUrl();

		$customer		= $_helper->getCurrentCustomer();
		$guestInvites	= Mage::getModel('invitations/config')->getGuestsCanMakeInvites();

		if(!($customer && $customer->getId()) && !$guestInvites){
			header('Location: '.Mage::helper('customer')->getLoginUrl());
			exit();
		}
	}


	//select plugin
	public function Step1Action() {

		$this->getResponse()->setBody(
				$this->getLayout()->createBlock('invitations/inviter_step1')->toHtml()
		);
	}

	public function Step2Action()
	{
		if (
			($providerBoxId = $this->getRequest()->getParam('provider_box'))
			&& ($addressBook = Mage::getModel('invitations/addressbooks')->load($providerBoxId))
			&& ($addressBook->isEnabled())
			&& ($addressBook->getStep2())
		)
		{
			Mage::register('current_address_book', $addressBook);

			if ($addressBook->getKey() == 'gmail'){
				header('Location: https://accounts.google.com/o/oauth2/auth?client_id='.$addressBook->getSettingByKey('client_id').'&redirect_uri=your_redirest_urls_goes_here&scope=https://www.google.com/m8/feeds/&response_type=code');
				return true;
			}

			$this->getResponse()->setBody(
				$this->getLayout()->createBlock('invitations/inviter_step2')->setAddressBook($addressBook)->toHtml()
			);
			return true;
		}
		$this->_redirect('*/*/step1');
	}

	//get contacts
	public function Step3Action()
	{
		$request = $this->getRequest();
		$errors = array();

		if (!$request->getParam('provider_box'))
			$errors['address_bok'] = Mage::helper('invitations')->__('Address book is not set!');
		else
		{
			$addressBook = Mage::getModel('invitations/addressbooks')->load($request->getParam('provider_box'));
			if (!$addressBook || !$addressBook->getId() || !$addressBook->isEnabled())
				$errors['address_bok'] = Mage::helper('invitations')->__('Address book is not enable!');
		}

		if (!count($errors))
		{
			Mage::register('current_address_book',$addressBook);
			$this->getRequest()->setParam('addressBookId', $addressBook->getId());
			switch($addressBook->getKey())
			{
				case 'facebook'	: $this->_step3Facebook($errors); break;
				case 'gmail'	: $this->_step3Gmail($errors); break;
				case 'yahoo'	: $this->_step3Yahoo($errors); break;
				case 'live'		: $this->_step3Live($errors); break;
				case 'mailru'	: $this->_step3Mailru($errors); break;
				//default			: $this->_step3Default($errors); break;
				default         : $this->_step3Error(array('login' => 'Address book is deprecated'));
			}
		} else {
			$this->_step3Error($errors);
		}
	}

	public function googleRedirectAction()
	{
		$addressBook = Mage::getModel('invitations/addressbooks')->getByKey('gmail');
		Mage::getSingleton('core/session')->setGoogleRedirectCode($this->getRequest()->getParam('code'));
		$this->_initCloseWindowBlock($addressBook);
	}

	public function liveRedirectAction()
	{
		$addressBook = Mage::getModel('invitations/addressbooks')->getByKey('live');
		Mage::getSingleton('core/session')->setLiveRedirectCode($this->getRequest()->getParam('code'));
		$this->_initCloseWindowBlock($addressBook);
	}

	public function mailruRedirectAction()
	{
		$addressBook = Mage::getModel('invitations/addressbooks')->getByKey('mailru');
		Mage::getSingleton('core/session')->setMailruRedirectCode($this->getRequest()->getParam('code'));
		$this->_initCloseWindowBlock($addressBook);
	}

	private function _step3Gmail($errors = array())
	{
		$contacts = array();

		try {
			$_helper = Mage::helper('invitations');
			$addressBook = Mage::registry('current_address_book');

			$client_id = $addressBook->getSettingByKey('client_id');
			$client_secret = $addressBook->getSettingByKey('client_secret');
			$redirect_uri =  Mage::getModel('core/url')->getUrl('*/*/googleRedirect');
			$max_results = 500;

			$auth_code = Mage::getSingleton('core/session')->getGoogleRedirectCode();

			$fields=array(
				'code'=>  urlencode($auth_code),
				'client_id'=>  urlencode($client_id),
				'client_secret'=>  urlencode($client_secret),
				'redirect_uri'=>  urlencode($redirect_uri),
				'grant_type'=>  urlencode('authorization_code')
			);
			$post = '';
			foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
			$post = rtrim($post,'&');

			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
			curl_setopt($curl,CURLOPT_POST,5);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
			$result = curl_exec($curl);
			curl_close($curl);

			$response =  json_decode($result);

			$result = null;
			if(!empty($response->access_token)) {
				$token = $response->access_token;
				$url = 'https://www.google.com/m8/feeds/contacts/default/full?alt=json&max-results='.$max_results.'&oauth_token='.$token;

				$json = null;
				if($response =  $_helper->curlFileGetContents($url)) {
					$json = json_decode($response);
				}
				if (!empty($json->error->internalReason)) {
					$errors[] = (string)$json->error->internalReason;
				} elseif (!empty($json->feed->entry) && is_array($json->feed->entry)) {
					foreach($json->feed->entry as $entry) {
						if(empty($entry->{'gd$email'})) continue;

						$contact = array(
							'email' => $entry->{'gd$email'}[0]->address,
							'name' => ($entry->title->{'$t'} != $entry->{'gd$email'}[0]->address ? $entry->title->{'$t'} : ''),
							'image' => '',
						);

						foreach ($entry->link as $link) {
							if($link->type == 'image/*' && (false !== strpos($link->rel, '#photo')) ) {
								$contact['image'] = $link->href ."?oauth_token={$token}";
							}
						}

						$contacts[ $contact['email'] ] = $contact;
					}
				}
			}

		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$this->getRequest()->setParam('contacts', $contacts);
		$this->_addErrorsFromArray($errors)->_initLayoutMessages('customer/session');
		$this->getResponse()->setBody(
			json_encode(array(
				'success'	=> true,
				'result'	=> $this->getLayout()->createBlock('invitations/inviter_step3')->setData('contacts', $contacts)->toHtml(),
			))
		);

	}

	protected function _addErrorsFromArray($errors)
	{
		$_sesssion = Mage::getModel('customer/session');
		foreach($errors as $error) {
			$_sesssion->addError($this->__($error));
		}
		return $this;
	}


	protected function _step3Yahoo($errors = array())
	{
		$contacts = array();

		try{
			$_helper = Mage::helper('invitations/yahoo');
			$guid = $this->getSession()->getInviterYahooGuid();
			$access_token = $this->getSession()->getInviterYahooAccessToken();
			$access_token_secret = $this->getSession()->getInviterYahooAccessTokenSecret();

			$retarr = $_helper->callectContacts($_helper->getOauthConsumerKey(), $_helper->getOauthConsumerSecret(), $guid, $access_token, $access_token_secret, false, true);
			if (!empty($retarr)) {
				$body = json_decode($retarr[2], true);
				if (!empty($body['query']['results']['contact'])){
					$user_contacts = $body['query']['results']['contact'];
					foreach($user_contacts as $contact){
						$email = '';
						$name = '';
						$fields = $contact['fields'];
						if (!isset($fields[0])){
							$fields = array($fields);
						}

						foreach($fields as $field){
							if ($field['type'] == 'email'){
								if ($field['value']){
									$email = $field['value'];
								}
							}
							if ($field['type'] == 'name'){
								if ($field['value']['givenName'] || $field['value']['familyName']){
									$name = ($field['value']['givenName'].' '.$field['value']['familyName']);
								}
							}
						}
						$name = $_helper->Unicode2Charset( $name  );
						if ($email) {
							$contact = array(
								'email' => $email,
								'name' => $name,
								'image' => '',
							);

							$contacts[$email] = $contact;
						}

					}
				} else {
					if (strpos(strtolower($retarr[2]), 'yahoo:error') !== false) {
						preg_match('/<yahoo:description>(.*)<\/yahoo:description>.*/i',  $retarr[2], $mathcRes);
						//$errors[] = $mathcRes[1];
					}
				}
			}

		} catch(Exception $e) {
			 $errors[] = $e->getMessage();
		}


		$this->_addErrorsFromArray($errors)->_initLayoutMessages('customer/session');
		$this->getRequest()->setParam('contacts', $contacts);
		$this->getResponse()->setBody(
			json_encode(array(
				'success'	=> true,
				'result'	=> $this->getLayout()->createBlock('invitations/inviter_step3')->setData('contacts', $contacts)->toHtml(),
			))
		);

	}

	private function _step3Facebook($errors = array()) {

		$fb = Mage::helper('invitations')->getFacebookApi();
		$userId = $fb->getUser();

		if($userId) {
			$contacts = $fb->api(array(
				'method' => 'fql.query',
				'query' => 'select uid, name, pic_square from user where uid in (select uid2 from friend where uid1='.$userId.')',
			));

			$this->getRequest()->setParam('contacts', $contacts);
			$this->getResponse()->setBody(
				$this->getLayout()->createBlock('invitations/inviter_step3')->setTemplate('invitations/inviter/step3/facebook.phtml')->toHtml()
			);
		}
		else
		{
			$errors['uid'] = Mage::helper('invitations')->__('Can not get facebook user');
			$this->_step3Error($errors);
		}

	}

	private function _step3Live($errors = array())
	{
		$contacts = array();

		try {
			$_helper = Mage::helper('invitations');
			$addressBook = Mage::registry('current_address_book');

			$client_id = $addressBook->getSettingByKey('client_id');
			$client_secret = $addressBook->getSettingByKey('client_secret');
			$redirect_uri =  Mage::getModel('core/url')->getUrl('*/*/liveRedirect');
			$max_results = 500;

			$auth_code = Mage::getSingleton('core/session')->getLiveRedirectCode();

			$fields=array(
				'code'=>  urlencode($auth_code),
				'client_id'=>  urlencode($client_id),
				'client_secret'=>  urlencode($client_secret),
				'redirect_uri'=>  urlencode($redirect_uri),
				'grant_type'=>  urlencode('authorization_code')
			);
			$post = '';
			foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
			$post = rtrim($post,'&');

			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,'https://login.live.com/oauth20_token.srf');
			curl_setopt($curl,CURLOPT_POST,5);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
			$result = curl_exec($curl);
			curl_close($curl);

			$response =  json_decode($result);
			if(!empty($response->access_token)) {
				$url = 'https://apis.live.net/v5.0/me/contacts?limit='. $max_results .'&access_token='. $response->access_token;

				$json = null;
				if($response =  $_helper->curlFileGetContents($url)) {
					$json = json_decode($response);
				}

				if (!empty($json->error->message)) {
					$errors[] = (string)$json->error->message;
				} elseif (!empty($json->data) && is_array($json->data)) {
					foreach($json->data as $item) {

						if(empty($item->emails->preferred)) continue;

						$contact = array(
							'email' => $item->emails->preferred,
							'name' => (!empty($item->name) && $item->name != $item->emails->preferred ? $item->name : ''),
							'image' => '',
						);

						$contacts[ $contact['email'] ] = $contact;
					}
				}
			}
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$this->getRequest()->setParam('contacts', $contacts);
		$this->_addErrorsFromArray($errors)->_initLayoutMessages('customer/session');
		$this->getResponse()->setBody(
			json_encode(array(
				'success'	=> true,
				'result'	=> $this->getLayout()->createBlock('invitations/inviter_step3')->setData('contacts', $contacts)->toHtml(),
			))
		);

	}

	private function _step3Mailru($errors = array())
	{
		$contacts = array();

		try {
			$_helper = Mage::helper('invitations');
			$addressBook = Mage::registry('current_address_book');

			$client_id = $addressBook->getSettingByKey('client_id');
			$client_secret = $addressBook->getSettingByKey('client_secret');
			$redirect_uri =  Mage::getModel('core/url')->getUrl('*/*/mailruRedirect');
			$max_results = 200; // 200 - max number identifiers for one request in mail api

			$auth_code = Mage::getSingleton('core/session')->getMailruRedirectCode();

			$fields=array(
				'code'=>  urlencode($auth_code),
				'client_id'=>  urlencode($client_id),
				'client_secret'=>  urlencode($client_secret),
				'redirect_uri'=>  urlencode($redirect_uri),
				'grant_type'=>  urlencode('authorization_code')
			);
			$post = '';
			foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
			$post = rtrim($post,'&');

			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,'https://connect.mail.ru/oauth/token');
			curl_setopt($curl,CURLOPT_POST,5);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
			$result = curl_exec($curl);
			curl_close($curl);

			$response =  json_decode($result);

			if(!empty($response->access_token)) {
				$sign = md5($t="app_id={$client_id}ext=1method=friends.getsecure=1session_key={$response->access_token}{$client_secret}");

				$params = array(
					'method'		=> 'friends.get',
					'secure'		=> '1',
					'app_id'		=> $client_id,
					'session_key'	=> $response->access_token,
					'sig'			=> $sign,
					'ext'			=> 1
				);

				$url = 'http://www.appsmail.ru/platform/api?'. urldecode(http_build_query($params));

				$json = null;
				if($responseUsers =  $_helper->curlFileGetContents($url)) {
					$userInfo = json_decode($responseUsers);
				}

				if (!empty($userInfo->error->error_msg)) {
					$errors[] = (string)$userInfo->error->error_msg;
				} elseif (!empty($userInfo) && is_array($userInfo)) {

					$userInfo = array_slice($userInfo, 0, $max_results);

					$mails = array(
						'mail'      => 'mail.ru',
						'mailua'    => 'mail.ua',
						'inbox'     => 'inbox.ru',
						'bk'        => 'bk.ru',
						'list'      => 'list.ru'
					);

					foreach ($userInfo as $key => $user) {
						preg_match('/https:\/\/my\.mail\.ru\/([A-Za-z]+)\/([0-9A-Za-z._-]+)/', $user->link, $email);
						if (isset($email[2]) && isset($email[1])) {
							$contact = array(
								'email' => $email[2] . '@' . $mails[$email[1]],
								'name'  => trim($user->first_name .' '. $user->last_name),
								'image' => (!empty($user->pic_32)? $user->pic_32 : ''),
							);
						}
						$contacts[ $contact['email'] ] = $contact;
					}
				}
			}

		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$this->getRequest()->setParam('contacts', $contacts);
		$this->_addErrorsFromArray($errors)->_initLayoutMessages('customer/session');
		$this->getResponse()->setBody(
			json_encode(array(
				'success'	=> true,
				'result'	=> $this->getLayout()->createBlock('invitations/inviter_step3')->setData('contacts', $contacts)->toHtml(),
			))
		);

	}

	public function _step3Error($errors = array()) {
		$this->getResponse()->setBody(
			json_encode(array(
				'error'		=> true,
				'result'	=> $errors,
			))
		);
	}

	public function getSession()
	{
		return  Mage::getSingleton('core/session');
	}

	/* Yahoo auth */
	public function yahooStep1Action()
	{
		$_helper = Mage::helper('invitations/yahoo');
		$callback = Mage::getUrl('*/*/yahooStep2');
		$retarr = $_helper->getRequestToken($_helper->getOauthConsumerKey(), $_helper->getOauthConsumerSecret(), $callback, false, true, true);
		if (! empty($retarr)){
			list($info, $headers, $body, $body_parsed) = $retarr;
			if ($info['http_code'] == 200 && !empty($body)) {
				$this->getSession()->setInviterYahooAccessTokenSecret($_helper->getDecoded($body_parsed['oauth_token_secret']));
				$this->getSession()->setInviterYahooAccessToken($_helper->getDecoded($body_parsed['oauth_token']));
				header('Location: '. $_helper->getDecoded($body_parsed['xoauth_request_auth_url']));
				exit();
			}
		}

		$this->_initCloseWindowBlock($_helper->getYahooABook());
	}

	public function yahooStep2Action()
	{
		$_helper = Mage::helper('invitations/yahoo');
		$_request = $this->getRequest();

		$request_token = $this->getSession()->getInviterYahooAccessToken();
		$request_token_secret =  $this->getSession()->getInviterYahooAccessTokenSecret();
		$oauth_verifier = $_request->getParam('oauth_verifier');

		$retarr = $_helper->getAccessToken($_helper->getOauthConsumerKey(), $_helper->getOauthConsumerSecret(), $request_token, $request_token_secret, $oauth_verifier, false, true, true);

		if (! empty($retarr)) {
			list($info, $headers, $body, $body_parsed) = $retarr;
			if ($info['http_code'] == 200 && !empty($body)) {
				$this->getSession()->setInviterYahooAccessTokenSecret($_helper->getDecoded($body_parsed['oauth_token_secret']));
				$this->getSession()->setInviterYahooAccessToken($_helper->getDecoded($body_parsed['oauth_token']));
				$this->getSession()->setInviterYahooGuid($_helper->getDecoded($body_parsed['xoauth_yahoo_guid']));
			}
		}

		$this->_initCloseWindowBlock($_helper->getYahooABook());
	}
	/* End Yahoo auth */

	protected function _initCloseWindowBlock($addressBook)
	{
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('invitations/inviter_step1')->setTemplate('invitations/inviter/step1/closewindow.phtml')->setAddressBook($addressBook)->toHtml()
		);
		return $this;
	}


}
