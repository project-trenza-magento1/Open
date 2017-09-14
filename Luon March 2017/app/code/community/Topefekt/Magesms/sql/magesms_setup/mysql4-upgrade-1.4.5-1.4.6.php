<?php
/**
 * Mage SMS - SMS notification & SMS marketing
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the BSD 3-Clause License
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/BSD-3-Clause
 *
 * @category    TOPefekt
 * @package     TOPefekt_Magesms
 * @copyright   Copyright (c) 2012-2015 TOPefekt s.r.o. (http://www.mage-sms.com)
 * @license     http://opensource.org/licenses/BSD-3-Clause
 */
 $iddb18dc4afa6663cf07a52c741943ff87cbe3896 = $this; $iddb18dc4afa6663cf07a52c741943ff87cbe3896->startSetup(); $ib678de385e13abb750f86db873c70fd899d5324c = array( array("Afghanistan",93), array("Andorra",376), array("Aruba",297), array("Benin",229), array("Brunei",673), array("Burkina Faso",226), array("Burundi",257), array("Cape Verde",238), array("CĂ´te d'Ivoire",225), array("Comoros",269), array("Congo Dem. R.",243), array("Cook Islands",682), array("Djibouti",253), array("Equatorial Guinea",240), array("Falkland Islands",500), array("Gabon",241), array("Gambia",220), array("Gibraltar",350), array("French Guiana",594), array("Guinea",224), array("Guinea-Bissau",245), array("Guyana",592), array("Haiti",509), array("Chad",235), array("Kiribati",686), array("Liberia",231), array("Mali",223), array("Mauritania",222), array("Mauritius",230), array("Montenegro",382), array("Mozambique",258), array("Netherlands Ant.",299), array("New Caledonia",687), array("Palau",680), array("RĂ©union",262), array("Saint Pierre a. M.",508), array("SĂŁo Tome a. P.",239), array("San Marino",378), array("Seychelles",248), array("Sierra Leone",232), array("Solomon Islands",677), array("Sudan",249), array("Suriname",597), array("Togo",228), array("Tuvalu",688), array("UAE",971), array("Uganda",256), array("Zambia",260), ); foreach ($ib678de385e13abb750f86db873c70fd899d5324c as $i04a044a36bef0ddde6d5de08f57f074024136d74) { $i037b855bc01175f2c77d5c3e19eda9a0003feff4 = Mage::getModel('magesms/country')->getCollection()->addFieldToFilter('name', $i04a044a36bef0ddde6d5de08f57f074024136d74[0]); if (!$i037b855bc01175f2c77d5c3e19eda9a0003feff4->count()) { $i037b855bc01175f2c77d5c3e19eda9a0003feff4 = Mage::getModel('magesms/country'); $i037b855bc01175f2c77d5c3e19eda9a0003feff4->setName($i04a044a36bef0ddde6d5de08f57f074024136d74[0])->setVat(0)->setCurrency('EUR'); $i037b855bc01175f2c77d5c3e19eda9a0003feff4->save(); $i819e44584c9e767fbcbdc7d78205ba711361e574 = Mage::getModel('magesms/country_area')->getCollection()->addFieldToFilter('area', $i04a044a36bef0ddde6d5de08f57f074024136d74[1]); if (!$i819e44584c9e767fbcbdc7d78205ba711361e574->count()) { $i819e44584c9e767fbcbdc7d78205ba711361e574 = Mage::getModel('magesms/country_area'); $i819e44584c9e767fbcbdc7d78205ba711361e574->setCountryName($i04a044a36bef0ddde6d5de08f57f074024136d74[0])->setArea($i04a044a36bef0ddde6d5de08f57f074024136d74[1]); $i819e44584c9e767fbcbdc7d78205ba711361e574->save(); } } } $iddb18dc4afa6663cf07a52c741943ff87cbe3896->endSetup(); 