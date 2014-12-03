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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('HBAgency', 'system/modules/zurb_foundation_articledropdown/library');

/**
 * Register classes outside the namespace folder
 */
NamespaceClassLoader::addClassMap(array
(
    
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    //Modules
	'mod_foundation_articledropdown'    => 'system/modules/zurb_foundation_articledropdown/templates/module',
	
	//Navigation
	'nav_dropdownarticle'               => 'system/modules/zurb_foundation_articledropdown/templates/navigation',
	
	//Foundation
	'foundation_dropdowncontent'        => 'system/modules/zurb_foundation_articledropdown/templates/foundation',
));
