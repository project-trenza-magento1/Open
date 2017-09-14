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


class Plumrocket_Invitations_Model_Sendfriend extends Mage_Sendfriend_Model_Sendfriend
{
    public function send()
    {
        $_helper = Mage::helper('invitations');
        $_config = Mage::getSingleton('invitations/config');
        $_session = Mage::getSingleton('customer/session');

        if (!$_helper->moduleEnabled() || !$_session->getCustomerId() || !$_config->invitationsViaShares()) {
            return parent::send();
        }


        if ($this->isExceedLimit()){
            Mage::throwException(Mage::helper('sendfriend')->__('You have exceeded limit of %d sends in an hour', $this->getMaxSendsToFriend()));
        }


        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);


        $mailTemplate = Mage::getModel('core/email_template');

        $message = nl2br(htmlspecialchars($this->getSender()->getMessage()));
        $sender  = array(
            'name'  => $this->_getHelper()->escapeHtml($this->getSender()->getName()),
            'email' => $this->_getHelper()->escapeHtml($this->getSender()->getEmail())
        );

        $mailTemplate->setDesignConfig(array(
            'area'  => 'frontend',
            'store' => Mage::app()->getStore()->getId()
        ));

        $productUrl = $_helper->addInviterToUrl($this->getProduct()->getUrlInStore(), $_session->getCustomerId());
        $websiteId = Mage::app()->getWebsite()->getId();
        $modelInvitations = Mage::getModel('invitations/invitations');

        foreach ($this->getRecipients()->getEmails() as $k => $email) {

            $name = $this->getRecipients()->getNames($k);

            $mailTemplate->sendTransactional(
                $this->getTemplate(),
                $sender,
                $email,
                $name,
                array(
                    'name'          => $name,
                    'email'         => $email,
                    'product_name'  => $this->getProduct()->getName(),
                    'product_url'   => $productUrl,
                    'message'       => $message,
                    'sender_name'   => $sender['name'],
                    'sender_email'  => $sender['email'],
                    'product_image' => Mage::helper('catalog/image')->init($this->getProduct(),
                        'small_image')->resize(75),
                )
            );


            /* save invitation */
            if (!Zend_Validate::is($email, 'EmailAddress') || $email == $_session->getCustomer()->getEmail()){
                continue;
            }

            $regCustomer = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('website_id', $websiteId)
                ->setPageSize(1)
                ->getFirstItem();

            if ($regCustomer->getId()) {
                continue;
            }

            $modelInvitations->invitee($email, $name, 0, $_session->getCustomerId(), null, $websiteId);
        }

        $translate->setTranslateInline(true);
        $this->_incrementSentCount();

        return $this;
    }
}
