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
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD']['navigationMenu'], 10, array
(
	'navarticle'	=> '\HBAgency\Module\FoundationArticleDropdown'
)); 
