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
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Newsletterpopup_Block_Adminhtml_Popups_Edit_Tabs_General_Coupon
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _toHtml()
    {
        return $this->allowGenerateCoupons()? 
            parent::_toHtml(): '';
    }

    protected function _prepareForm()
    {
        $popup = Mage::registry('popup');
        $disabled = $popup->getCouponCode() == 0;

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('popup_');
		
        $fieldset = $form->addFieldset('coupon_fieldset', array('legend' => Mage::helper('newsletterpopup')->__('Coupons Format')));

        $fieldset->addType('extended_label','Plumrocket_Newsletterpopup_Block_Adminhtml_Popups_Edit_Renderer_Label');

        $fieldset->addField('just_label', 'extended_label', array(
            'hidden'    => ! $disabled
        ));
        $popup->setData('just_label', Mage::helper('newsletterpopup')->__('Coupon code is not selected in the General section above. Coupons Format is disabled.'));

        $fieldset->addField('code_length', 'text', array(
            'name'     => 'code_length',
            'label'    => Mage::helper('newsletterpopup')->__('Code Length'),
            'required' => true,
            'note'     => Mage::helper('newsletterpopup')->__('Excluding prefix, suffix and separators.'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'disabled' => $disabled,
        ));

        $formats = array();
        if ($this->allowGenerateCoupons()) {
            $formats = Mage::helper('salesrule/coupon')->getFormatsList();
        }
        $fieldset->addField('code_format', 'select', array(
            'label'    => Mage::helper('newsletterpopup')->__('Code Format'),
            'name'     => 'code_format',
            'options'  => $formats,
            'required' => true,
            'disabled' => $disabled,
        ));

        $fieldset->addField('code_prefix', 'text', array(
            'name'  => 'code_prefix',
            'label' => Mage::helper('newsletterpopup')->__('Code Prefix'),
            'disabled' => $disabled,
        ));

        $fieldset->addField('code_suffix', 'text', array(
            'name'  => 'code_suffix',
            'label' => Mage::helper('newsletterpopup')->__('Code Suffix'),
            'disabled' => $disabled,
        ));

        $fieldset->addField('code_dash', 'text', array(
            'name'  => 'code_dash',
            'label' => Mage::helper('newsletterpopup')->__('Dash Every X Characters'),
            'note'  => Mage::helper('newsletterpopup')->__('If empty no separation.'),
            'class' => 'validate-digits',
            'disabled' => $disabled,
        ));

        $form->setValues($popup->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function allowGenerateCoupons()
    {
        $mageVersion = Mage::getVersionInfo();
        return $mageVersion['major'] == '1' && $mageVersion['minor'] >= 7;
    }
}
