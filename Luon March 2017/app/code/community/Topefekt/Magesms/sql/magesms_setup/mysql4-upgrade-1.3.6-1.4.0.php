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
 $iddb18dc4afa6663cf07a52c741943ff87cbe3896 = $this; $iddb18dc4afa6663cf07a52c741943ff87cbe3896->startSetup(); $i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce = Mage::getModel('magesms/maps')->getCollection()->addFieldToFilter('area', 237)->addFieldToFilter('number', 9); if (!$i4f3b75abfeef0eea3f3858aa24b2cf7c9edfa6ce->count()) { Mage::getModel('magesms/maps')->setArea(237)->setNumber(9)->save(); } Mage::getConfig()->reinit(); Mage::app()->reinitStores(); $ie54fcd5470bd7f31f709089290e33bb03e655c25 = array(); foreach (Mage::app()->getStores() as $i3763a59a4c1873eeb396b46caa87140ccb7bc631) { $ia35eb6e21739b9f362d4085ebe7dee274bc421a7 = Mage::getModel('core/store')->load($i3763a59a4c1873eeb396b46caa87140ccb7bc631->getId())->getConfig('general/locale/code'); if (!in_array($ia35eb6e21739b9f362d4085ebe7dee274bc421a7, array_values($ie54fcd5470bd7f31f709089290e33bb03e655c25))) { $ie54fcd5470bd7f31f709089290e33bb03e655c25[] = $ia35eb6e21739b9f362d4085ebe7dee274bc421a7; } } if (count($ie54fcd5470bd7f31f709089290e33bb03e655c25)) { $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235 = Mage::getModel('core/resource_transaction'); $i3f9de7a8e56927ebf8381b5412baaf4548ab1e62 = Mage::getModel('magesms/hooks_customers')->getCollection()->addFieldToFilter('mutation', array('like' => '__\_%')); foreach ($i3f9de7a8e56927ebf8381b5412baaf4548ab1e62 as $ia95e2cbdd7c4f15529016efe3018555ad75ee3e4) { $ia95e2cbdd7c4f15529016efe3018555ad75ee3e4->isDeleted(true); $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->addObject($ia95e2cbdd7c4f15529016efe3018555ad75ee3e4); } $i5873f2ce330009cf680967439e8b5bc4dcc47b65 = Mage::getModel('magesms/hooks_customers')->getCollection()->addFieldToFilter('mutation', 'default'); foreach ($i5873f2ce330009cf680967439e8b5bc4dcc47b65 as $i816ffec1f9a32e2bde09d40428fad957ae5edba0) { $i6a8c141169d48ca0fd1da7ba133093ee034712b6 = false; foreach ($ie54fcd5470bd7f31f709089290e33bb03e655c25 as $i593f9fb6306ab4cdb862f1ef6769504d63647c90) { if ($i6a8c141169d48ca0fd1da7ba133093ee034712b6) { $i816ffec1f9a32e2bde09d40428fad957ae5edba0->setMutation($i593f9fb6306ab4cdb862f1ef6769504d63647c90); $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->addObject($i816ffec1f9a32e2bde09d40428fad957ae5edba0); $i6a8c141169d48ca0fd1da7ba133093ee034712b6 = false; } else { $i21e55df616c305955791876c1eb4da83448beba2 = Mage::getModel('magesms/hooks_customers'); $i21e55df616c305955791876c1eb4da83448beba2->addData($i816ffec1f9a32e2bde09d40428fad957ae5edba0->getData()); $i21e55df616c305955791876c1eb4da83448beba2->unsId(); $i21e55df616c305955791876c1eb4da83448beba2->setMutation($i593f9fb6306ab4cdb862f1ef6769504d63647c90); $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->addObject($i21e55df616c305955791876c1eb4da83448beba2); } } } $idfa032f3c6e5ae7a5ed2f2c1ee51cca1f877d8ab = Mage::getModel('magesms/hooks_unicode')->getCollection() ->addFieldToFilter('area', array('like' => '__\_%'))->addFieldToFilter('type', 'customer'); foreach ($idfa032f3c6e5ae7a5ed2f2c1ee51cca1f877d8ab as $i7d1f5efb0c774f1e6b3d8e66cea1bdb630249d67) { $i7d1f5efb0c774f1e6b3d8e66cea1bdb630249d67->isDeleted(true); $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->addObject($i7d1f5efb0c774f1e6b3d8e66cea1bdb630249d67); } $i737316bf7223562994c418fae8d8e2b2e808c67b = Mage::getModel('magesms/hooks_unicode')->getCollection() ->addFieldToFilter('area', 'default')->addFieldToFilter('type', 'customer'); foreach ($i737316bf7223562994c418fae8d8e2b2e808c67b as $i0c01e8a28c3997e59de75905c4e02365535c6181) { $i6a8c141169d48ca0fd1da7ba133093ee034712b6 = false; foreach ($ie54fcd5470bd7f31f709089290e33bb03e655c25 as $i593f9fb6306ab4cdb862f1ef6769504d63647c90) { if ($i6a8c141169d48ca0fd1da7ba133093ee034712b6) { $i0c01e8a28c3997e59de75905c4e02365535c6181->setArea($i593f9fb6306ab4cdb862f1ef6769504d63647c90); $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->addObject($i0c01e8a28c3997e59de75905c4e02365535c6181); $i6a8c141169d48ca0fd1da7ba133093ee034712b6 = false; } else { $ie8d90f6313614fbb6564426c0b0cb59a0ca4cecd = Mage::getModel('magesms/hooks_unicode'); $ie8d90f6313614fbb6564426c0b0cb59a0ca4cecd->setArea($i593f9fb6306ab4cdb862f1ef6769504d63647c90); $ie8d90f6313614fbb6564426c0b0cb59a0ca4cecd->setUnicode($i0c01e8a28c3997e59de75905c4e02365535c6181->getUnicode()); $ie8d90f6313614fbb6564426c0b0cb59a0ca4cecd->setType($i0c01e8a28c3997e59de75905c4e02365535c6181->getType()); $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->addObject($ie8d90f6313614fbb6564426c0b0cb59a0ca4cecd); } } } $i47f954bfb9dd4be93a5c46b2c8260d3fbc064235->save(); } $iddb18dc4afa6663cf07a52c741943ff87cbe3896->endSetup(); 