<?php

class tx_kbeventboard_addCategoriesToFlexForm {
 function addCategories ($config) {

	// Vars:
	$uid = $config['row']['uid'];
	$recursive = $config['row']['recursive'];
	if($config['row']['recursive']=="")$recursive = 0;
	$selectedPids = "";

	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pages','tt_content','uid = '.$uid,'');

	//Print Record:
	$pagesIdList = "";
	if (!empty($res)) {
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$pagesIdList = explode(",",$row['pages']);
	}
	if(!intval($pagesIdList[0])){
		$pagesIdList[0] = $config['row']['pid'];
	}

	foreach($pagesIdList as $index => $root) {
		$selectedPids .= (($index == 0)?"":",").$this->getRecursiveUidList($root,$recursive);
	}

	$selectedPidsList = explode(',',$selectedPids);
	$selectedPidsList = array_unique($selectedPidsList);
    
	foreach($selectedPidsList as $pageId){
		$subSql .= "pid = " . $pageId ." OR ";
	 }
	 $subSql = substr($subSql, 0, -3);
	
	$optionList = array();
	$i = 0;
	// Show Categories:
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kbeventboard_category',$subSql,'category');

	//Print Records:
	if (!empty($res)) {
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$optionList[$i] = array(0 => $row['category'], 1 => $row['uid']);
			$i++;
		}
	}else{
		return "";
	}
   $config['items'] = array_merge($config['items'],$optionList);
   return $config;
 }
 
 
	/**
	 * Generates a list of pids of all sub pages for the given depth.
	 *
	 * @param	integer		the pid of the page
	 * @param	integer		the depth for the search
	 * @return	string		the list of pids
	 * @author	of method	Michael Oehlhof <typo3@oehlhof.de>
	 * @access public
	 */
	function getRecursiveUidList($parentUid, $depth){
		global $TCA;

		if($depth != -1) {
			$depth = $depth-1; //decreasing depth
		}
		# Get ressource records:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'uid',
			'pages',
			'pid IN (' . $GLOBALS['TYPO3_DB']->cleanIntList($parentUid) . ') '
				. t3lib_BEfunc::deleteClause('pages')
				. t3lib_BEfunc::versioningPlaceholderClause('pages')
			);
		if($depth > 0){
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$parentUid .= ',' . $this->getRecursiveUidList($row['uid'], $depth);
			}
		}
		return $parentUid;
	} 
 
} // end class


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_eventboard/class.tx_kbeventboard_addCategoriesToFlexForm.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_eventboard/class.tx_kbeventboard_addCategoriesToFlexForm.php']);
}

?>