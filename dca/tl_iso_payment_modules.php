<?php

/**
 * Extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 terminal42 gmbh
 *
 * @package    isotope_viveum
 * @link       http://www.terminal42.ch
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['viveum'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,trans_type,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},viveum_pspid,viveum_dynamic_template,viveum_hash_in,viveum_hash_out;{price_legend:hide},price,tax_class;{expert_legend:hide},guests,protected;{enabled_legend},debug,enabled';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['viveum_pspid'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['viveum_pspid'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['viveum_dynamic_template'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['viveum_dynamic_template'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('maxlength'=>255, 'tl_class'=>'w50', 'rgxp'=>'url')
);
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['viveum_hash_in'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['viveum_hash_in'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'tl_class'=>'w50'),
);
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['viveum_hash_out'] = array
(
    'label'                 => &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['viveum_hash_out'],
    'exclude'               => true,
    'inputType'             => 'text',
    'eval'                  => array('mandatory'=>true, 'maxlength'=>32, 'tl_class'=>'w50'),
);