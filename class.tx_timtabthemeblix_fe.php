<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Ingo Renner (typo3@ingo-renner.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * class.tx_timtabthemeblix_fe.php
 *
 * Class to localize strings and do custom theme stuff
 *
 * $Id$
 *
 * @author Ingo Renner <typo3@ingo-renner.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

#$PATH_timtab = t3lib_extMgm::extPath('timtab');
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_timtabthemeblix_fe extends tslib_pibase {
	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId 		= 'tx_timtabthemeblix_fe';		// Same as class name
	var $scriptRelPath 	= 'class.tx_timtabthemeblix_fe.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'timtab_theme_blix';	// The extension key.

	var $conf;
	var $markerArray;

	/**
	 * main function which executes all steps
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
	 */
	function main($markerArray, $conf) {		
		$this->init($markerArray, $conf);
		$this->substituteMarkers();

		return $this->markerArray;
	}

	/**
	 * initializes the configuration for the extension
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	void
	 */
	function init($markerArray, $conf) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.
		$this->pi_loadLL(); // Loading language-labels
		
		$this->conf['allowCaching'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tt_news.']['allowCaching'];
		$this->conf['blogPid'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_timtab.']['blogPid'];
		
		$this->markerArray = $markerArray;
	}

	/**
	 * substitutes markers like count of comments
	 *
	 * @return	void
	 */
	function substituteMarkers() {
		if($this->calledBy == 'tt_news') {
			$this->markerArray['###BLIX_MORE_LINK###']    = $this->getMoreLink($this->conf['data']);
			$this->markerArray['###BLIX_COMMENT_LINK###'] = $this->getCommentLink($this->conf['data']);
			$this->markerArray['###BLIX_LATEST_HEADER###'] = $this->pi_getLL('most_recent');
		
			$filedUnder = $this->pi_getLL('filed_under');
			$this->markerArray['###BLIX_FILED_UNDER###'] = str_replace(
				'###CATEGORIES###',
				$this->markerArray['###NEWS_CATEGORY###'],
				$filedUnder
			);
		} elseif($this->calledBy == 've_guestbook') {		
			$this->markerArray['###BLIX_ADD_YOUR_COMMENT###'] = $this->getCommentFormLink();	
			$this->markerArray['###BLIX_LEAVE_COMMENT###'] = $this->pi_getLL('leave_comment');	
			$this->markerArray['###BLIX_REQUIRED###'] = $this->pi_getLL('required');
			$this->markerArray['###BLIX_HIDDEN###'] = $this->pi_getLL('hidden');
			$this->markerArray['###BLIX_COMMENT###'] = $this->pi_getLL('comment');
			$this->markerArray['###BLIX_TRACKBACK###'] = $this->pi_getLL('trackback');
			$this->markerArray['###FORM_SUBMIT###'] = $this->pi_getLL('submit');	
		}
	}


	/**
	 * builds a link to a given post
	 * 
	 * @param	array		the post data
	 * @return	string		the post link
	 */
	function getMoreLink($tt_news) {
		$urlParams = array(
			'tx_ttnews[year]'    => date('Y', $tt_news['datetime']),
			'tx_ttnews[month]'   => date('m', $tt_news['datetime']),
			'tx_ttnews[day]'     => date('d', $tt_news['datetime']),
			'tx_ttnews[tt_news]' => $tt_news['uid']
		);

		$tagAttribs = ' class="more"';

		$conf = array(
			'useCacheHash'     => $this->conf['allowCaching'],
			'no_cache'         => !$this->conf['allowCaching'],
			'parameter'        => $this->conf['blogPid'],
			'additionalParams' => $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',$urlParams,'',1).$this->pi_moreParams,
			'ATagParams'       => $tagAttribs
		);

		return $this->cObj->typoLink($this->pi_getLL('continue'), $conf);
	}
	
	/**
	 * builds a link to a given post
	 * 
	 * @param	array		the post data
	 * @return	string		the post link
	 */
	function getCommentLink($tt_news) {
		$urlParams = array(
			'tx_ttnews[year]'    => date('Y', $tt_news['datetime']),
			'tx_ttnews[month]'   => date('m', $tt_news['datetime']),
			'tx_ttnews[day]'     => date('d', $tt_news['datetime']),
			'tx_ttnews[tt_news]' => $tt_news['uid']
		);

		$tagAttribs = ' class="commentlink"';

		$conf = array(
			'useCacheHash'     => $this->conf['allowCaching'],
			'no_cache'         => !$this->conf['allowCaching'],
			'parameter'        => $this->conf['blogPid'],
			'additionalParams' => $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',$urlParams,'',1).$this->pi_moreParams,
			'ATagParams'       => $tagAttribs,
			'section'          => 'comments'
		);
		
		$link = $this->pi_getLL('add_comment');
		if($this->markerArray['###BLOG_COMMENTS_COUNT###']) {
			$link = $this->markerArray['###BLOG_COMMENTS_COUNT###'].' '
					.$this->markerArray['###BLOG_TEXT_COMMENTS###'];
		}

		return $this->cObj->typoLink($link, $conf);
	}
	
	/**
	 * builds a link to the comment form in a post singleview
	 * 
	 * @param	array		the post data
	 * @return	string		the post link
	 */
	function getCommentFormLink() {		
		$get = t3lib_div::_GET('tx_ttnews');
		$urlParams = array(
			'tx_ttnews[year]'    => $get['year'],
			'tx_ttnews[month]'   => $get['month'],
			'tx_ttnews[day]'     => $get['day'],
			'tx_ttnews[tt_news]' => $get['tt_news']
		);

		$conf = array(
			'useCacheHash'     => $this->conf['allowCaching'],
			'no_cache'         => !$this->conf['allowCaching'],
			'parameter'        => $GLOBALS['TSFE']->id,
			'additionalParams' => $this->conf['parent.']['addParams'].t3lib_div::implodeArrayForUrl('',$urlParams,'',1).$this->pi_moreParams,
			'ATagParams'       => ' class="more"',
			'section'          => 'commentform'
		);
		
		return $this->cObj->typoLink($this->pi_getLL('add_your_own'), $conf);
	}
	
	/***********************************************
	 *
	 * Hook Connectors
	 *
	 **********************************************/

	/**
	 * connects into tt_news and ve_guestbook item marker processing hook
	 * and fills our markers
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the current tt_news record
	 * @param	array		the configuration coming from tt_news
	 * @param	object		the parent object calling this method
	 * @return	array		processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->conf['data'] = $row;
		$this->pObj = &$pObj;
		$this->calledBy = $pObj->extKey; //who is calling? 

		return $this->main($markerArray, $lConf);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab_theme_blix/class.tx_timtabthemeblix_fe.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/timtab_theme_blix/class.tx_timtabthemeblix_fe.php']);
}

?>