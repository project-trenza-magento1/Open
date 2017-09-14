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
 $iddb18dc4afa6663cf07a52c741943ff87cbe3896 = $this; $iddb18dc4afa6663cf07a52c741943ff87cbe3896->startSetup(); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->run("
INSERT INTO `{$this->getTable('magesms_hooks')}` (`name`, `info`, `owner`, `group`, `background`, `icon`, `template`, `template2`, `notice`, `lang`) VALUES
('customerRegisterSuccess', 'Kundkonto skapades med lyckat resultat', 3, 2, '', '', 'Customer  {customer_firstname} {customer_lastname}, id: {customer_id}, has just subscribed to {shop_name}.', 'Dear {customer_firstname} {customer_lastname}, your account in {shop_name} was successfully created. Your username: {customer_email}. Have a nice day!', '{customer_id}, {customer_email}, {customer_password}, {customer_lastname}, {customer_firstname}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}', 'sv'),
('newOrder', 'Nya beställningar', 3, 1, '', '', 'New order from {customer_firstname} {customer_lastname}, customer id: {customer_id}, order id: {order_id}, payment: {order_payment}, amount: {order_total_paid} {order_currency}. Order: {order_products2}. Info from {shop_name}.', 'Your order {order_id} was successfully created, payment: {order_payment}, amount: {order_total_paid} {order_currency}. Have a nice day, {shop_name}.', '{customer_id}, {customer_email}, {customer_company}, {customer_lastname}, {customer_firstname}, {customer_address}, {customer_postcode}, {customer_city}, {customer_country}, {customer_state}, {customer_phone}, {customer_invoice_company}, {customer_invoice_lastname}, {customer_invoice_firstname}, {customer_invoice_address}, {customer_invoice_postcode}, {customer_invoice_city}, {customer_invoice_country}, {customer_invoice_state}, {customer_invoice_phone}, <br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}<br /><br />{order_id}, {order_payment}, {order_payment_html}, {order_total_paid}, {order_currency}, {order_date}, {order_date1}, {order_date2}, {order_date3}, {order_date4}, {order_date5}, {order_date6}, {order_date7}, {order_time}, {order_time1}<br /><br /> {newOrder1}, {newOrder2}, {newOrder3}, {newOrder4}, {newOrder5}<br /><br />{cart_id}, {customer_message}', 'sv'),
('updateOrderStatus', 'Uppdatering av beställningsstatus', 3, 0, '', '', 'Status för beställning {order_id} har ändrats till {{order_status_name}}. Information från {shop_name}.', 'Bäste kund, status för din beställning {order_id} har ändrats till {{order_status_name}}. Ha en trevlig dag, {shop_name}.', ' {customer_id}, {customer_email}, {customer_company}, {customer_lastname}, {customer_firstname}, {customer_address}, {customer_postcode}, {customer_city}, {customer_country}, {customer_state}, {customer_phone}, {customer_vat_number}, {customer_invoice_company}, {customer_invoice_lastname}, {customer_invoice_firstname}, {customer_invoice_address}, {customer_invoice_postcode}, {customer_invoice_city}, {customer_invoice_country}, {customer_invoice_state}, {customer_invoice_phone}, {customer_invoice_vat_number}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}<br /><br />{order_id}, {order_payment}, {order_payment_html}, {order_total_paid}, {order_currency}, {order_date}, {order_date1}, {order_date2}, {order_date3}, {order_date4}, {order_date5}, {order_date6}, {order_date7}, {order_time}, {order_time1}, {order_shipping_number}, {order_reference}, {carrier_name}<br /><br />{employee_id}, {employee_email}', 'sv'),
('contactForm', 'Kontaktformulär', 3, 4, '', '', '{customer_email} - {customer_message}', '', '{customer_email}, {customer_name}, {customer_phone}{customer_message}, {customer_message_short1}, {customer_message_short2}, {customer_message_short3}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}', 'sv'),
('updateOrderTrackingNumber', 'Uppdatering av spårningsnummer för ', 3, 1, '', '', 'Beställning {order_id} uppdaterades, spårningsnummret är {order_shipping_number}. Information från {shop_name}.', 'Bäste kund, din beställning {order_id} skickades, spårningsnummret är {order_shipping_number}. Ha en trevlig dag, {shop_name}.', ' {customer_id}, {customer_email}, {customer_company}, {customer_lastname}, {customer_firstname}, {customer_address}, {customer_postcode}, {customer_city}, {customer_country}, {customer_state}, {customer_phone}, {customer_vat_number}, {customer_invoice_company}, {customer_invoice_lastname}, {customer_invoice_firstname}, {customer_invoice_address}, {customer_invoice_postcode}, {customer_invoice_city}, {customer_invoice_country}, {customer_invoice_state}, {customer_invoice_phone}, {customer_invoice_vat_number}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}<br /><br />{order_id}, {order_payment}, {order_payment_html}, {order_total_paid}, {order_currency}, {order_date}, {order_date1}, {order_date2}, {order_date3}, {order_date4}, {order_date5}, {order_date6}, {order_date7}, {order_time}, {order_time1}, {order_shipping_number}, {order_reference}, {carrier_name}<br /><br />{employee_id}, {employee_email}', 'sv'),
('createCreditMemo', 'Called when a quantity of one product change in an order', 3, 1, '', '', 'In order {order_id} was changed quantity, {creditMemo2}. Info from {shop_name}.', 'In your order {order_id} was changed quantity, {creditMemo2}. Have a nice day, {shop_name}.', ' {customer_id}, {customer_email}, {customer_company}, {customer_lastname}, {customer_firstname}, {customer_address}, {customer_postcode}, {customer_city}, {customer_country}, {customer_state}, {customer_phone}, {customer_vat_number}, {customer_invoice_company}, {customer_invoice_lastname}, {customer_invoice_firstname}, {customer_invoice_address}, {customer_invoice_postcode}, {customer_invoice_city}, {customer_invoice_country}, {customer_invoice_state}, {customer_invoice_phone}, {customer_invoice_vat_number}<br /><br />{shop_domain}, {shop_name}, {shop_email}, {shop_phone}<br /><br />{order_id}, {order_payment}, {order_payment_html}, {order_total_paid}, {order_currency}, {order_date}, {order_date1}, {order_date2}, {order_date3}, {order_date4}, {order_date5}, {order_date6}, {order_date7}, {order_time}, {order_time1}, {order_shipping_number}, {order_reference}, {carrier_name}<br /><br />{creditMemo1}, {creditMemo2},  {creditMemo3}, {creditMemo4}, {creditMemo5}\r\n<br /><br />{employee_id}, {employee_email}', 'sv'),
('productOutOfStock', 'Produkten slut i lager', 3, 3, '', '', 'This product is out of stock, id: {product_id}, ref: {product_ref}, name: {product_name}, current  quantity: {product_quantity}. Info from {shop_name}.', '', '{shop_domain}, {shop_name}, {shop_email}, {shop_phone}<br /><br />{customer_id}, {customer_email}, {customer_lastname}, {customer_firstname}<br /><br />{product_id}, {product_quantity}, {product_name}, {product_ref}, {product_supplier_ref}, {product_ean13}, {product_upc}, {product_supplier_id}, {product_supplier}', 'sv'),
('productLowStock', 'Produkten slut i lager', 3, 3, '', '', 'This product is out of stock, id: {product_id}, ref: {product_ref}, name: {product_name}, current  quantity: {product_quantity}. Info from {shop_name}.', '', '{shop_domain}, {shop_name}, {shop_email}, {shop_phone}<br /><br />{customer_id}, {customer_email}, {customer_lastname}, {customer_firstname}<br /><br />{product_id}, {product_quantity}, {product_name}, {product_ref}, {product_supplier_ref}, {product_ean13}, {product_upc}, {product_supplier_id}, {product_supplier}', 'sv');

UPDATE `{$this->getTable('magesms_country_lang')}` SET `iso2` = 'sv' WHERE `country_name` LIKE 'Sweden';

"); $iddb18dc4afa6663cf07a52c741943ff87cbe3896->endSetup(); 