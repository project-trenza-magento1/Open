<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_MegaMenu
 * @copyright   Copyright (c) 2014-2016 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

$this->startSetup();

$this->addAttribute('catalog_category', 'is_packery', array(
    'group'             => 'Menu',
    'backend'           => '',
    'frontend'          => '',
    'class'             => '',
    'default'           => '1',
    'label'             => 'Enable Packery',
    'note'              => 'The packery layout mode uses a bin-packing algorithm. It positions subcategory blocks by filling the available space and removing vertical gaps.',
    'input'             => 'select',
    'type'              => 'int',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'searchable'        => 0,
    'filterable'        => 0,
    'comparable'        => 0,
    'required'          => 0,
    'unique'            => 0,
    'user_defined'      => 1,
));

$this->endSetup();
