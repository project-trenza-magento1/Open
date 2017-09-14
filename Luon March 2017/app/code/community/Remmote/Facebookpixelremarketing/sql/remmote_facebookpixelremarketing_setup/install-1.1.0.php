<?php
//Including product attributes
$this->addAttribute('catalog_product', 'facebook_product_catalog', array(
    'type'          => 'varchar',
    'label'         => 'Use for Facebook Product Catalog',
    'note'          => 'Set to "Yes" if you want to export this product to your Facebook product catalog.',
    'input'         => 'select',
    'source'        => 'eav/entity_attribute_source_boolean',
    'sort_order'    => 10,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'      => 0,
    'user_defined'  => true,
    'group'         => 'Facebook Product Catalog',
));

$this->addAttribute('catalog_product', 'facebook_product_description', array(
    'type'          => 'varchar',
    'label'         => 'Facebook Product Description',
    'note'          => 'If you want to use a different description for this product on Facebook, use this field to enter a custom product description.',
    'input'         => 'textarea',
    'sort_order'    => 20,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'      => 0,
    'user_defined'  => true,
    'group'         => 'Facebook Product Catalog',
));

//Including category attribute
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'remmote_google_taxonomy', array(
    'group'         => 'General Information',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Google Product Taxonomy',
    'note'          => 'If you are using Google product taxonomy, enter the taxonomy for your category here. <a href="https://support.google.com/merchants/answer/6324436" target="_blank">Learn more here</a>.',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));