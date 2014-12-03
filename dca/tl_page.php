<?php

/**
 * Zurb Foundation dropdown article navigation for Contao Open Source CMS
 *
 * Copyright (C) 2014 HB Agency
 *
 * @package    Zurb_Foundation_ArticleDropdown
 * @link       http://www.hbagency.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace('type', 'type;{nav_legend},navarticle,navdataoptions', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] );
$GLOBALS['TL_DCA']['tl_page']['palettes']['forward'] = str_replace('type', 'type;{nav_legend},navarticle,navdataoptions', $GLOBALS['TL_DCA']['tl_page']['palettes']['forward'] );
$GLOBALS['TL_DCA']['tl_page']['palettes']['redirect'] = str_replace('type', 'type;{nav_legend},navarticle,navdataoptions', $GLOBALS['TL_DCA']['tl_page']['palettes']['redirect'] );

$GLOBALS['TL_DCA']['tl_page']['fields']['navarticle'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['navarticle'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('\HBAgency\Backend\Page\ArticleCallback', 'getArticles'),
	'eval'                    => array('chosen'=>true, 'includeBlankOption'=> true, 'submitOnChange'=>true),
	'wizard' => array
	(
		array('\HBAgency\Backend\Page\ArticleCallback', 'editArticle')
	),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['navdataoptions'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['navdataoptions'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('tl_class'=>'clr w50'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);