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


namespace HBAgency\Module;

/**
 * Class FoundationArticleDropdown
 *
 * Front end module "navigation", but with articles for dropdowns
 * @copyright  2014 HB Agency
 * @author     Blair Winans <bwinans@hbagency.com>
 * @package    Zurb_Foundation
 */
class FoundationArticleDropdown extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_foundation_articledropdown';
	
	/**
	 * Template
	 * @var string
	 */
	protected $articleTemplate = 'foundation_dropdowncontent';

    /**
	 * Array of articles to parse
	 * @var string
	 */
	protected $arrArticles = array();


	/**
	 * Do not display the module if there are no menu items
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### DROPDOWN NAVIGATION MENU with ARTICLES ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$strBuffer = parent::generate();
		return strlen($this->Template->items) ? $strBuffer : '';
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{	
		global $objPage;

		$trail = $objPage->trail;
		$level = ($this->levelOffset > 0) ? $this->levelOffset : 0;

		// Overwrite with custom reference page
		if ($this->defineRoot && $this->rootPage > 0)
		{
			$trail = array($this->rootPage);
			$level = 0;
		}

		$this->Template->request = $this->getIndexFreeRequest(true);
		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->skipNavigation = specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
		$this->Template->items = $this->renderNavigation($trail[$level]);
		$this->Template->articles = count($this->arrArticles) > 0 ? implode("\n", $this->arrArticles) : '';
	}
	
	/**
	 * Recursively compile the navigation menu and return it as HTML string
	 * @param integer
	 * @param integer
	 * @return string
	 */
	protected function renderNavigation($pid, $level=1, $host=null, $language=null)
	{
		$time = time();

		// Get all active subpages
		$objSubpages = \PageModel::findPublishedSubpagesWithoutGuestsByPid($pid, $this->showHidden, $this instanceof \ModuleSitemap);

		if ($objSubpages === null)
		{
			return '';
		}

		$items = array();
		$groups = array();

		// Get all groups of the current front end user
		if (FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');
			$groups = $this->User->groups;
		}

		// Layout template fallback
		if ($this->navigationTpl == '')
		{
			$this->navigationTpl = 'nav_default';
		}

		$objTemplate = new \FrontendTemplate($this->navigationTpl);

		$objTemplate->type = get_class($this);
		$objTemplate->level = 'level_' . $level++;

		// Get page object
		global $objPage;

		// Browse subpages
		while($objSubpages->next())
		{
			// Skip hidden sitemap pages
			if ($this instanceof \ModuleSitemap && $objSubpages->sitemap == 'map_never')
			{
				continue;
			}

			$subitems = '';
			$_groups = deserialize($objSubpages->groups);
			
			// Override the domain (see #3765)
			if ($host !== null)
			{
				$objSubpages->domain = $host;
			}

			// Do not show protected pages unless a back end or front end user is logged in
			if (!$objSubpages->protected || BE_USER_LOGGED_IN || (is_array($_groups) && count(array_intersect($_groups, $groups))) || $this->showProtected || ($this instanceof \ModuleSitemap && $objSubpages->sitemap == 'map_always'))
			{
				// Check whether there will be subpages
				if ($objSubpages->subpages > 0 && (!$this->showLevel || $this->showLevel >= $level || (!$this->hardLimit && ($objPage->id == $objSubpages->id || in_array($objPage->id, $this->getChildRecords($objSubpages->id, 'tl_page'))))))
				{
					$subitems = $this->renderNavigation($objSubpages->id, $level);
				}

				// Get href
				switch ($objSubpages->type)
				{
					case 'redirect':
						$href = $objSubpages->url;

						if (strncasecmp($href, 'mailto:', 7) === 0)
						{
							$this->import('String');
							$href = $this->String->encodeEmail($href);
						}
						break;

					if ($objSubpages->jumpTo)
						{
							$objNext = $objSubpages->getRelated('jumpTo');
						}
						else
						{
							$objNext = \PageModel::findFirstPublishedRegularByPid($objSubpages->id);
						}

						if ($objNext !== null)
						{
							$strForceLang = null;
							$objNext->loadDetails();

							// Check the target page language (see #4706)
							if ($GLOBALS['TL_CONFIG']['addLanguageToUrl'])
							{
								$strForceLang = $objNext->language;
							}

							$href = $this->generateFrontendUrl($objNext->row(), null, $strForceLang);

							// Add the domain if it differs from the current one (see #3765)
							if ($objNext->domain != '' && $objNext->domain != \Environment::get('host'))
							{
								$href = (\Environment::get('ssl') ? 'https://' : 'http://') . $objNext->domain . TL_PATH . '/' . $href;
							}
							break;
						}
						// DO NOT ADD A break; STATEMENT

					default:
					    $href = $this->generateFrontendUrl($objSubpages->row(), null, $language);
					
						// Add the domain if it differs from the current one (see #3765)
						if ($objSubpages->domain != '' && $objSubpages->domain != \Environment::get('host'))
						{
							$href = (\Environment::get('ssl') ? 'https://' : 'http://') . $objSubpages->domain . TL_PATH . '/' . $href;
						}
						break;
				}

				// Active page
				if (($objPage->id == $objSubpages->id || $objSubpages->type == 'forward' && $objPage->id == $objSubpages->jumpTo) && !$this instanceof ModuleSitemap && !$this->Input->get('articles'))
				{
					$strClass = (($subitems != '') ? 'submenu' : '') . ($objSubpages->protected ? ' protected' : '') . (($objSubpages->cssClass != '') ? ' ' . $objSubpages->cssClass : '');
					$row = $objSubpages->row();

					$row['isActive'] = true;
					$row['subitems'] = $subitems;
					$row['dropdownarticle'] = $this->getDropdown($objSubpages->navarticle, $strClass);
					$row['class'] = trim($strClass);
					$row['title'] = specialchars($objSubpages->title, true);
					$row['pageTitle'] = specialchars($objSubpages->pageTitle, true);
					$row['link'] = $objSubpages->title;
					$row['href'] = $href;
					$row['nofollow'] = (strncmp($objSubpages->robots, 'noindex', 7) === 0);
					$row['target'] = '';
					$row['description'] = str_replace(array("\n", "\r"), array(' ' , ''), $objSubpages->description);

					// Override the link target
					if ($objSubpages->type == 'redirect' && $objSubpages->target)
					{
						$row['target'] = ($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"';
					}

					$items[] = $row;
				}

				// Regular page
				else
				{
					$strClass = (($subitems != '') ? 'submenu' : '') . ($objSubpages->protected ? ' protected' : '') . (($objSubpages->cssClass != '') ? ' ' . $objSubpages->cssClass : '') . (in_array($objSubpages->id, $objPage->trail) ? ' trail' : '');

					// Mark pages on the same level (see #2419)
					if ($objSubpages->pid == $objPage->pid)
					{
						$strClass .= ' sibling';
					}

					$row = $objSubpages->row();

					$row['isActive'] = false;
					$row['subitems'] = $subitems;
					$row['dropdownarticle'] = $this->getDropdown($objSubpages->navarticle, $strClass);
					$row['class'] = trim($strClass);
					$row['title'] = specialchars($objSubpages->title, true);
					$row['pageTitle'] = specialchars($objSubpages->pageTitle, true);
					$row['link'] = $objSubpages->title;
					$row['href'] = $href;
					$row['nofollow'] = (strncmp($objSubpages->robots, 'noindex', 7) === 0);
					$row['target'] = '';
					$row['description'] = str_replace(array("\n", "\r"), array(' ' , ''), $objSubpages->description);

					// Override the link target
					if ($objSubpages->type == 'redirect' && $objSubpages->target)
					{
						$row['target'] = ($objPage->outputFormat == 'xhtml') ? ' onclick="return !window.open(this.href)"' : ' target="_blank"';
					}

					$items[] = $row;
				}
			}
		}

		// Add classes first and last
		if (!empty($items))
		{
			$last = count($items) - 1;

			$items[0]['class'] = trim($items[0]['class'] . ' first');
			$items[$last]['class'] = trim($items[$last]['class'] . ' last');
		}

		$objTemplate->items = $items;
		return !empty($items) ? $objTemplate->parse() : '';
	}
	
	
	/**
	 * Generate and return an articleID for the dropdown
	 * @param integer
	 * @param string
	 * @return string|boolean false
	 */
	protected function getDropdown($intId, $strClass='')
	{
		$varArticleID = false;
		
		$objArticle = \ArticleModel::findByPk($intId);
		
		if($objArticle !== null)
		{
		    $varArticleID = $objArticle->alias;
		
		    $objTemplate = new \FrontendTemplate($this->articleTemplate);
            $objTemplate->article = \Controller::getArticle($objArticle->id, false, true);
            $objTemplate->id = $varArticleID;
            
            //Add to articles array
            $this->arrArticles[] = $objTemplate->parse();
		}
		
		return $varArticleID;
	}

	
}