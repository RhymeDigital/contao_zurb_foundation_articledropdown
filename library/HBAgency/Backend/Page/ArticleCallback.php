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

namespace HBAgency\Backend\Page;

/**
 * Class ArticleCallback
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class ArticleCallback extends \Backend
{

	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Return the edit article teaser wizard
	 * @param DataContainer
	 * @return string
	 */
	public function editArticle(\DataContainer $dc)
	{
		$this->loadLanguageFile('tl_content');
		return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=article&amp;table=tl_article&amp;act=edit&amp;id=' . $dc->value . '" title="'.sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editarticle'][1]), $dc->value).'">' . $this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editarticle'][0], 'style="vertical-align:top"') . '</a>';
	}


	/**
	 * Get all articles and return them as array (article teaser)
	 * @param DataContainer
	 * @return array
	 */
	public function getArticles(\DataContainer $dc)
	{
		$arrPids = array();
		$arrArticle = array();
		$arrRoot = array();
		$intPid = $dc->activeRecord->id;

		if (\Input::get('act') == 'overrideAll')
		{
			$intPid = \Input::get('id');
		}

		if ($dc->activeRecord->id)
		{
			$objPage = $this->getPageDetails($dc->activeRecord->id);
			$arrRoot = $this->getChildRecords($objPage->rootId, 'tl_page');
			array_unshift($arrRoot, $objPage->rootId);
		}

		// Limit pages to the user's pagemounts
		if ($this->User->isAdmin)
		{
			$objArticle = $this->Database->execute("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid" . (!empty($arrRoot) ? " WHERE a.pid IN(". implode(',', array_map('intval', array_unique($arrRoot))) .")" : "") . " ORDER BY parent, a.sorting");
		}
		else
		{
			foreach ($this->User->pagemounts as $id)
			{
				if (!in_array($id, $arrRoot))
				{
					continue;
				}

				$arrPids[] = $id;
				$arrPids = array_merge($arrPids, $this->getChildRecords($id, 'tl_page'));
			}

			if (empty($arrPids))
			{
				return $arrArticle;
			}

			$objArticle = $this->Database->execute("SELECT a.id, a.pid, a.title, a.inColumn, p.title AS parent FROM tl_article a LEFT JOIN tl_page p ON p.id=a.pid WHERE a.pid IN(". implode(',', array_map('intval', array_unique($arrPids))) .") ORDER BY parent, a.sorting");
		}

		// Edit the result
		if ($objArticle->numRows)
		{
			$this->loadLanguageFile('tl_article');

			while ($objArticle->next())
			{
				$key = $objArticle->parent . ' (ID ' . $objArticle->pid . ')';
				$arrArticle[$key][$objArticle->id] = $objArticle->title . ' (' . (strlen($GLOBALS['TL_LANG']['tl_article'][$objArticle->inColumn]) ? $GLOBALS['TL_LANG']['tl_article'][$objArticle->inColumn] : $objArticle->inColumn) . ', ID ' . $objArticle->id . ')';
			}
		}

		return $arrArticle;
	}
}