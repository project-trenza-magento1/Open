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

class Plumrocket_Invitations_Model_System_Config_Source_Acceptemailwhen
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('invitations')->__('Invitee places first order')),
            array('value' => 1, 'label'=>Mage::helper('invitations')->__('Invitee creates an account')),
        );
    }

}
