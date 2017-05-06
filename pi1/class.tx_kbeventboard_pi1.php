<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Klaus Biedermann <klaus.biedermann@gmx.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
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

// require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Event Board' for the 'kb_eventboard' extension.
 *
 * @author	Klaus Biedermann <klaus.biedermann@gmx.de>
 * @package	TYPO3
 * @subpackage	tx_kbeventboard
 */
class tx_kbeventboard_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_kbeventboard_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_kbeventboard_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'kb_eventboard';	// The extension key.
	
	// init vars:
	var $eventFolder;
	var $img_pfad;
	var $extPath;  // Path to extension
	var $extRelPath;  // rel Path to extension
	var $today;
	var $include_css;
	var $aktUserGroup;
	var $spanindex;
	var $tmpl;
	var $sel_categories;
	var $step;
	var $showmode;
	var $pos;
	var $startingpointRecursiveLevel;  // recursive Datasource selection Level: returns this values "",1,3,4,250
	var $selectedcategory;
	var $categorySelector;
	var $javascriptFkt;
	var $showLocation;
	var $showMorelink;
	var $showTitleLink;
	var $morelinkId;
	var $eventImages;
	var $eventid;
	var $show_time_mode;
	var $ascDesc;
	var $eventFolderSQLPart;
	var $showImages;
	var $showAllImages;
	
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{

		// Init:
		$this->init($conf);

		// Showmodes:
		switch ( $this->showmode )	{
		  case 1:
		    $content = $this->showStandard();
		  break;
		  case 2:
		    $content = $this->showSingleView();
		  break;
		  case 3:
		    $content = $this->showFrontpage();
		  break;
		  case 4:
		    $content = $this->showContextCol();
		  break;
		  default:
		    $content = $this->showStandard();
		  break;
		}
		return $content;
	}
	
	
	/**
	 * Init Function: here all the needed configuration values are stored in class variables..
	 *
	 * @param	array		$conf : configuration array from TS
	 * @return	void
	 */
	function init($conf){
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		//Get Eventcontainer-PID -- Set actual page if no container is specified
		$this->eventFolder = explode(',', $this->cObj->data['pages']);
		
		// if there is no startingpoint, use the current page as eventfolder:
		if(!intval($this->eventFolder[0])){
			$this->eventFolder[0]=$GLOBALS['TSFE']->id;
		}
		
		$this->eventFolderSQLPart = ' AND (ev.pid='.$this->eventFolder[0] .'';
		if(count($this->eventFolder>1)){
			for($i=1;$i<count($this->eventFolder);$i++) {
				$this->eventFolderSQLPart .=  ' OR ev.pid='.$this->eventFolder[$i] .'';
			}
		}
		$this->eventFolderSQLPart .=  ')';
		
		// startingpointRecursiveLevel:
		$this->startingpointRecursiveLevel = $this->cObj->data['recursive'];
		
		// Path to extension:
		$this->extPath = t3lib_extmgm::extPath($this->extKey);
		$this->extRelPath = t3lib_extmgm::siteRelPath($this->extKey);

	
		//Set Vars
		$content = "";
		$this->img_pfad = 'uploads/tx_kbeventboard/';
		$this->yesterday = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
		$this->today = time();
		
		$this->aktUserGroup = $GLOBALS['TSFE']->fe_user->groupData[uid];
		if(empty($this->aktUserGroup)) {$this->aktUserGroup = array(0 => '0');} 
		
		$this->spanindex = 0;
		
		// get data from Flexform:
		$this->pi_initPIflexForm();
		
		// loading template:
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template');
		if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template') != ""  &&  strchr ( $templateflex_file, 'fileadmin' )){
			$templateflex_file = explode("fileadmin" , $templateflex_file);
			$templateflex_file = "fileadmin" . $templateflex_file[1];
		}else{
			$templateflex_file = 'EXT:kb_eventboard/template.tmpl';
		}	
		$this->tmpl = $this->cObj->fileResource($templateflex_file);

		// some vars:
		$this->sel_categories = array_unique (explode(',',$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'sel_categories', 's_template')));
		$this->step = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'step', 's_template') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'step', 's_template') : $this->conf["step"];
		$this->csseven = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'csseven', 's_template');
		$this->cssodd = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cssodd', 's_template');
		$this->showmode = $this->piVars['showmode'] ? $this->piVars['showmode'] : $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showmode', 's_template');
		$this->showLocation = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'show_location', 's_template');
		$this->show_time_mode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'show_time_mode', 's_template');
		$this->showMorelink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'show_morelink', 's_template');
		$this->showTitleLink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showTitleLink', 's_template');
		$this->morelinkId = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'morelink', 's_template');
		$this->eventid = $this->piVars['evt'] ? $this->piVars['evt'] : t3lib_div::_GET('evt');
		$this->eventid = intval($this->eventid);
		$this->ascDesc = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascDesc', 's_template') ? ' DESC' : ' ASC';
		$this->include_css = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'include_css', 's_template');
		$this->showImages = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showImages', 's_template');
		$this->showAllImages = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showAllImages', 's_template');
	}

	/**
	 * showStandard Function: shows the events with category selector and navigation buttons.
	 *
	 * @param	-
	 * @return	content
	 */
	function showStandard() {
		// piVars aus GET:
		if(isset($this->piVars['selectedcategory'])){
			$this->selectedcategory = $this->piVars['selectedcategory'];
		}else{
			$this->selectedcategory = $GLOBALS['TYPO3_DB']->cleanIntList(t3lib_div::_POST('selectedcategory')) ? $GLOBALS['TYPO3_DB']->cleanIntList(t3lib_div::_POST('selectedcategory')) : 0;
		}
		$this->pos = $this->piVars['pos'] ? $this->piVars['pos'] : 0;
				
		$this->categorySelector = "";

		$ttcontentID = explode(":", $this->cObj->currentRecord);
		$ttcontentID = $ttcontentID[1];
	
		//Javascript Function
		$this->javascriptFkt = '
		<script type="text/javascript">
		/*<![CDATA[*/
		
		gSteps = '.$this->step.';
		kb_eventboard_ctime = 0;
		function showDiv'.$ttcontentID.'(infofeld){
			for(i=0;i<=gSteps;i++){
				tmpId = "text_'.$ttcontentID.'_"+i+"";
				if(document.getElementById(tmpId)){
					document.getElementById(tmpId).style.display="none";
				}
			}
			document.getElementById(infofeld).style.display="inline";
			if(kb_eventboard_ctime > 0){
				clearTimeout(kb_eventboard_ctime);
				kb_eventboard_ctime = 0;
			}
		}
		
		function hideDiv(infofeld){
		  document.getElementById(infofeld).style.display="none";
		}
		/*]]>*/
		</script>';
		
		// Show Categories:

		$pagesIdList = $this->eventFolder;
		foreach($pagesIdList as $index => $root) {
			$selectedPids .= (($index == 0)?"":",").$this->getRecursiveUidList($root,$this->startingpointRecursiveLevel);
		}
		$selectedPidsList = explode(',',$selectedPids);
		$selectedPidsList = array_unique($selectedPidsList);
		
		foreach($selectedPidsList as $pageId){
			$subSql .= "pid = " . $pageId ." OR ";
		 }
		$subSql = substr($subSql, 0, -3);
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kbeventboard_category',$subSql,'category');		
	
		//Print Records:
		$this->categorySelector .= '<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" id="kb_eventboard" name="kb_eventboard" method="post"><select id="selectedcategory" name="selectedcategory" size="1">';
		$this->categorySelector .= '<option value="0">'.$this->pi_getLL("allcategories").'</option>';
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {			
			if ( in_array ( $row['uid'], $this->sel_categories ) ){
				$this->categorySelector .= '<option value="'.$row['uid'].'"';
				if($this->selectedcategory == $row['uid']){ $this->categorySelector .= 'selected'; };
				$this->categorySelector .= '>'.htmlspecialchars($row['category']).'</option>';
			}
		}
		$this->categorySelector .= '</select><input type="submit" name="sendcategory" value="OK"></form>';

		for ($i=0; $i<count($selectedPidsList); $i++) {
		    if ($i==0) {
			$sqlpids = "(ev.pid = ".$selectedPidsList[0];
		    } else {
			$sqlpids = $sqlpids." OR ev.pid = $selectedPidsList[$i]";
		    }
		}
		$sqlpids = $sqlpids." AND ev.pid = lo.pid)";
		
		// ########## BEGIN COUNT DATA ##################
		if($this->selectedcategory > 0){
		
			if($this->show_time_mode == 1){ // all events
				
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
									      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
									      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
									      AND ev.location = lo.uid 
										  AND (FIND_IN_SET('.$this->selectedcategory.',ev.category))','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '');
										  
			}else if($this->show_time_mode == 2){ // only old events
				
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
									      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
									      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
									      AND ev.location = lo.uid 
										  AND (FIND_IN_SET('.$this->selectedcategory.',ev.category))
										  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '');
										  
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
								      AND ev.hidden=0 AND '.$sqlpids.' 
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND (FIND_IN_SET('.$this->selectedcategory.',ev.category))
								      AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '');
			}
		}else{
				
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid ','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '');								  
			}else if($this->show_time_mode == 2){ // only old events
			
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '');
									  
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
							      AND ev.hidden=0 AND '.$sqlpids.' 
							      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
							      AND ev.location = lo.uid 
							      AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '');
			}
		}
		$count = $GLOBALS["TYPO3_DB"]->sql_num_rows($res);
		// ########## END COUNT DATA ####################
	
		//Get Data
		if($this->selectedcategory > 0){
		
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
									      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
									      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
									      AND ev.location = lo.uid 
										  AND (FIND_IN_SET('.$this->selectedcategory.',ev.category))','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '',''.$this->pos.','.$this->step.'');
						  
			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
									      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
									      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
									      AND ev.location = lo.uid 
										  AND (FIND_IN_SET('.$this->selectedcategory.',ev.category))
										  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '',''.$this->pos.','.$this->step.'');
						  
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
								      AND ev.hidden=0 AND '.$sqlpids.' 
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND (FIND_IN_SET('.$this->selectedcategory.',ev.category))
								      AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '',''.$this->pos.','.$this->step.'');
			}
									  
		}else{
				
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo, tx_kbeventboard_category ca','ev.deleted=0 
								      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND ev.category = ca.uid','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '',''.$this->pos.','.$this->step.'');
			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo, tx_kbeventboard_category ca','ev.deleted=0 
								      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND ev.category = ca.uid 
									  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '',''.$this->pos.','.$this->step.'');
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo, tx_kbeventboard_category ca','ev.deleted=0 
							      AND ev.hidden=0 AND '.$sqlpids.' 
							      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
							      AND ev.location = lo.uid 
							      AND ev.category = ca.uid 
							      AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . ', ev.startingtime '. $this->ascDesc . '',''.$this->pos.','.$this->step.'');
			}
		}
		// if no records:
		if(!$res){
			return htmlspecialchars($this->pi_getLL('norecords'));
		}
		

		// Vars for templating:
		$tmpContent = "";
		$marker = array();
		$markerBase = array();
		$markerFooter = array();	

		// Subpart Header:
		$this->tmplHeader = $this->cObj->getSubpart($this->tmpl, "###EVENTBOARD###");
		// Subpart Events:
		$this->tmplEvent = $this->cObj->getSubpart($this->tmpl, "###EVENT_STANDARDVIEW###");

		// Subpart Footer:
		$this->tmplFooter = $this->cObj->getSubpart($this->tmpl, "###EVENTBOARDFOOTER###");
		
		if($this->include_css){
			$markerBase["###CSSSTYLE###"] = '<link rel="stylesheet" type="text/css" media="screen" href="typo3conf/ext/kb_eventboard/kb_eventboard.css" />';
		}else{
			$markerBase["###CSSSTYLE###"] = '';
		}
		
		if($this->showLocation){
			$markerBase["###JAVASCRIPTFKT###"] = $this->javascriptFkt;
		}else{
			$markerBase["###JAVASCRIPTFKT###"] = '';
		}
		$markerBase["###LL_HEADLINE###"] = htmlspecialchars($this->pi_getLL('headline'));
		$markerBase["###CATEGORYSELETOR###"] = $this->categorySelector;
		
		$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplHeader, $markerBase);
		
		//Print Records
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			// even_odd style sheet class:
			$marker["###EVEN_ODD###"] =  ($this->spanindex % 2 == 0) ? " ".$this->csseven : " ". $this->cssodd;
		
			if($row['price'] == '') {
				$aktPrice = htmlspecialchars($this->pi_getLL('priceFree'));
			} else {
				$aktPrice = $row['price'].' '.htmlspecialchars($this->pi_getLL('currency'));
			}
			$marker["###PRICE###"] = $aktPrice;
			
			//Check daylight saving time -- Is that the right way???
			$startingtime = $row['startingtime'];
			
			$marker["###CATEGORYDESCRIPTION###"] = htmlspecialchars($row['categorydescription']);
			$marker["###DATUM###"] = $this->cObj->stdWrap($row['datebegin'], $this->conf['showStandard.']['date_stdWrap.']);			
			if($row['dateend'] > 0 && $row['dateend'] != $row['datebegin']){
				$marker["###ENDDATUM###"] = '- ' . $this->cObj->stdWrap($row['dateend'], $this->conf['showStandard.']['date_stdWrap.']);
			}else {
				$marker["###ENDDATUM###"] = '';
			}
			$marker["###CONTENT_ID###"] = $ttcontentID;
			
			if($this->showLocation){
				$marker["###DIV_IDS###"] = 'text_'.$ttcontentID.'_'.$this->spanindex;
				
				$marker["###CLOSEBOX###"] = '<img src="typo3conf/ext/kb_eventboard/res/closebtn.gif" alt="close-button" />';
				
				$marker["###LOCATION###"] = htmlspecialchars($row['locationname']);
				$marker["###STREET###"] = htmlspecialchars($row['street']);
				$marker["###ZIP###"] = htmlspecialchars($row['zip']);
				$marker["###CITY###"] = htmlspecialchars($row['city']);
				// commented 16.06.2014 by Pascal Alich, getImage does not work in TYPO3 6.2.x even if no image is present
				$marker["###PIC###"] = ''/*$this->getImage($row['logo'],$row['alttext'],$row['titletext']) . '<div class="kb_eventboard_eventImageSubtitle">' . $row['subtitletext'] . '</div>'*/;
				
				$marker["###LOCATIONDESCRIPTION###"] = htmlspecialchars($row['locationdescription']);
				$marker["###HOMEPAGE###"] = htmlspecialchars($row['homepage']);
				
				$marker["###LL_LOCATIONHP###"] = htmlspecialchars($this->pi_getLL('locationHP'));
				
				$marker["###LL_LOCATIONTITLE###"] = $this->pi_getLL('locationTitle');
				
				$marker["###LINKCLASS###"] = 'infolink';
			}else{
				$marker["###DIV_IDS###"] = '';
				
				$marker["###CLOSEBOX###"] = '';
				
				$marker["###LOCATION###"] = '';
				$marker["###STREET###"] = '';
				$marker["###ZIP###"] = '';
				$marker["###CITY###"] = '';
				$marker["###PIC###"] = '';
				
				$marker["###LOCATIONDESCRIPTION###"] = '';
				$marker["###HOMEPAGE###"] = '';
				
				$marker["###LL_LOCATIONHP###"] = '';
				
				$marker["###LL_LOCATIONTITLE###"] = '';
				
				$marker["###LINKCLASS###"] = '';
			}

			/* event images */
			if($this->showImages){
				if($row['images'] != ""){
					// if more then 1 image:
					$imageList = explode(',',$row['images']);
					$imageAltTagList = explode(PHP_EOL,$row['imagesalt']);
					$imageTitleTagList = explode(PHP_EOL,$row['imagestitle']);
					$imageSubTitleTextList = explode(PHP_EOL,$row['imagessubtitle']);
					$this->eventImages = "";
					
					if($this->showAllImages){
						for($i=0;$i<count($imageList);$i++){
							$this->eventImages .= $this->getEventImage($imageList[$i],'eventpicsSingle.',$imageAltTagList[$i],$imageTitleTagList[$i]);
							$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitle">' . $imageSubTitleTextList[$i] . '</div>';
						}
					}else{
						$this->eventImages .= $this->getEventImage($imageList[0],'eventpicsSingle.',$imageAltTagList[0],$imageTitleTagList[0]);
						$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitle">' . $imageSubTitleTextList[0] . '</div>';
					}
				}else{
					$this->eventImages = "";
				}
				$marker["###IMAGES###"] = $this->eventImages;
			}else{
				$marker["###IMAGES###"] = "";
			}
			
			if(intval($this->showMorelink) == 1){
				$marker["###TEASERDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['teaserdescription']));
				// empty the others:
				$marker["###EVENTDESCRIPTION###"] = "";
			}else{
				$marker["###TEASERDESCRIPTION###"] = "";
				$marker["###EVENTDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['eventdescription']));
			}
			
			$marker["###LL_STARTTIME###"] = htmlspecialchars($this->pi_getLL('starttime'));

			if(intval($startingtime) > 0){
				$marker["###STARTTIME###"] = gmstrftime('%H:%M', $startingtime);
			}else{
				$marker["###STARTTIME###"] = '-';
			}
			
			// added 29.03.2014 by Pascal Alich
			$marker["###HOMEPAGE_LINE###"] = (strlen(trim($marker["###HOMEPAGE###"])) > 0) ? '<br/>Website: <a href="'.$marker["###HOMEPAGE###"].'" target="_blank">'.$marker["###HOMEPAGE###"].'</a>' : '';
			$marker["###EVENTDESCRIPTION_LINE###"] = (strlen(trim($marker["###EVENTDESCRIPTION###"])) > 0) ? $marker["###EVENTDESCRIPTION###"].'<br/>' : '';
			$marker["###LOCATIONDESCRIPTION_LINE###"] = (strlen(trim($marker["###LOCATIONDESCRIPTION###"])) > 0) ? $marker["###LOCATIONDESCRIPTION###"].'<br/>' : '';
	
			// Adds hook for processing of extra markers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showStandardMarkerHook'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showStandardMarkerHook'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$marker = $_procObj->showStandardMarker($marker, $row, $this->conf, $this);
				}
			}
			
			$marker["###LL_PRICE###"] = htmlspecialchars($this->pi_getLL('price'));
		
			// more Link anchor
			$marker["###EVENTANCHOR###"] = 'eventanchor'.$row['uid'];
			// more Link:
			if(intval($this->showMorelink) == 1){
				$marker["###MORE_LINK###"] = '<a href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_morelink" >'.htmlspecialchars($this->pi_getLL('morelink')).'</a>';
				// set Link title:
				if(intval($this->showTitleLink) == 1){
					$marker["###EVENTNAME###"] = '<a href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_titlelink" >'.htmlspecialchars($row['eventname']).'</a>';
				}else{
					$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);
				}
			}else{
				$marker["###MORE_LINK###"] = '';
				$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);
			}

			$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplEvent, $marker);
			$this->spanindex ++;
		} // end while

		
		if($this->pos > 0){
			// prev Button:
			$tmppos = $this->pos - $this->step;
			$footerLeft = "<div style='float: left'><a class='kb_eventboard-prevlink' href='". $this->pi_linkTP_keepPIvars_url(Array('selectedcategory' => $this->selectedcategory, 'step' => $this->step, 'pos' => $tmppos),1). "' >".$this->pi_getLL('prevlinklabel')."</a></div>";		
		}else{
			$footerLeft = "";
		}

		if($count > ($this->pos+$this->step)){
			// next Button:
			$tmppos = $this->pos + $this->step;
			$footerRight = "<div style='float: right'><a class='kb_eventboard-nextlink' href='". $this->pi_linkTP_keepPIvars_url(Array('selectedcategory' => $this->selectedcategory, 'step' => $this->step, 'pos' => $tmppos),1). "' >".$this->pi_getLL('nextlinklabel')."</a></div>";
		}else{
			$footerRight = "";
		}
		
		$markerFooter["###FOOTERLEFT###"] = $footerLeft;
		$markerFooter["###FOOTERRIGHT###"] = $footerRight;

		$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplFooter, $markerFooter);

		return $tmpContent;
	}


	
	
	
	
	
	
	
	
//###########################################################################	
	
	/**
	 * showSingleView Function: shows one event in single view.
	 *
	 * @param	-
	 * @return	content
	 */
	function showSingleView() {
		// piVars aus GET:
		if(isset($this->piVars['selectedcategory'])){
			$this->selectedcategory = $this->piVars['selectedcategory'];
		}else{
			$this->selectedcategory = $GLOBALS['TYPO3_DB']->cleanIntList(t3lib_div::_POST('selectedcategory')) ? $GLOBALS['TYPO3_DB']->cleanIntList(t3lib_div::_POST('selectedcategory')) : 0;
		}
		$this->pos = $this->piVars['pos'] ? $this->piVars['pos'] : 0;
		
		$tmpContent = '';
		
		if($this->eventid > 0){

			$this->categorySelector = "";

			$ttcontentID = explode(":", $this->cObj->currentRecord);
			$ttcontentID = $ttcontentID[1];
		
			//Javascript Function
			$this->javascriptFkt = '
			<script type="text/javascript">
			/*<![CDATA[*/
			
			gSteps = '.$this->step.';
			kb_eventboard_ctime = 0;
			function showDiv'.$ttcontentID.'(infofeld){
				for(i=0;i<=gSteps;i++){
					tmpId = "text_'.$ttcontentID.'_"+i+"";
					if(document.getElementById(tmpId)){
						document.getElementById(tmpId).style.display="none";
					}
				}
				document.getElementById(infofeld).style.display="inline";
				if(kb_eventboard_ctime > 0){
					clearTimeout(kb_eventboard_ctime);
					kb_eventboard_ctime = 0;
				}
			}
			
			function hideDiv(infofeld){
			  document.getElementById(infofeld).style.display="none";
			}
			/*]]>*/
			</script>';
				

			//Get Data
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								      AND ev.hidden=0  
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND ev.uid = '.$this->eventid.'','','ev.datebegin '. $this->ascDesc . '');
								  
			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								      AND ev.hidden=0  
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND ev.uid = '.$this->eventid.'
								      AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '');
								  
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								      AND ev.hidden=0  
								      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								      AND ev.location = lo.uid 
									  AND ev.uid = '.$this->eventid.'
								      AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '');			
			}
			// if no records:
			if(!$res){
				return htmlspecialchars($this->pi_getLL('norecords'));
			}

			// Vars for templating:
			$tmpContent = "";
			$marker = array();
			$markerBase = array();
			$markerFooter = array();	

			// Subpart Header:
			$this->tmplHeader = $this->cObj->getSubpart($this->tmpl, "###EVENTBOARD###");
			// Subpart Events:
			$this->tmplEvent = $this->cObj->getSubpart($this->tmpl, "###EVENT_SINGLEVIEW###");

			// Subpart Footer:
			$this->tmplFooter = $this->cObj->getSubpart($this->tmpl, "###EVENTBOARDFOOTER###");
			
			if($this->include_css){
				$markerBase["###CSSSTYLE###"] = '<link rel="stylesheet" type="text/css" media="screen" href="typo3conf/ext/kb_eventboard/kb_eventboard.css" />';
			}else{
				$markerBase["###CSSSTYLE###"] = '';
			}
			
			if($this->showLocation){
				$markerBase["###JAVASCRIPTFKT###"] = $this->javascriptFkt;
			}else{
				$markerBase["###JAVASCRIPTFKT###"] = '';
			}
			$markerBase["###LL_HEADLINE###"] = htmlspecialchars($this->pi_getLL('headline'));
			$markerBase["###CATEGORYSELETOR###"] = $this->categorySelector;
			
			$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplHeader, $markerBase);
			
			//Print Records
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			
				// even_odd style sheet class:
				$marker["###EVEN_ODD###"] =  ($this->spanindex % 2 == 0) ? " ".$this->csseven : " ". $this->cssodd;
				
				
				if($row['price'] == '') {
					$aktPrice = htmlspecialchars($this->pi_getLL('priceFree'));
				} else {
					$aktPrice = $row['price'].' '.htmlspecialchars($this->pi_getLL('currency'));
				}
				$marker["###PRICE###"] = $aktPrice;
				
				//Check daylight saving time -- Is that the right way???
				$startingtime = $row['startingtime'];
				$marker["###DATUM###"] = $this->cObj->stdWrap($row['datebegin'], $this->conf['showSingleView.']['date_stdWrap.']);			
				if($row['dateend'] > 0 && $row['dateend'] != $row['datebegin']){
					$marker["###ENDDATUM###"] = '- ' . $this->cObj->stdWrap($row['dateend'], $this->conf['showSingleView.']['date_stdWrap.']);
				}else {
					$marker["###ENDDATUM###"] = '';
				}
				$marker["###CONTENT_ID###"] = $ttcontentID;
				
				if($this->showLocation){
					$marker["###DIV_IDS###"] = 'text_'.$ttcontentID.'_'.$this->spanindex;
					
					$marker["###CLOSEBOX###"] = '<img src="typo3conf/ext/kb_eventboard/res/closebtn.gif" alt="close-button" />';
					
					$marker["###LOCATION###"] = htmlspecialchars($row['locationname']);
					$marker["###STREET###"] = htmlspecialchars($row['street']);
					$marker["###ZIP###"] = htmlspecialchars($row['zip']);
					$marker["###CITY###"] = htmlspecialchars($row['city']);
					$marker["###PIC###"] = $this->getImage($row['logo'],$row['alttext'],$row['titletext']) . '<div class="kb_eventboard_eventImageSubtitle">' . $row['subtitletext'] . '</div>';
					
					$marker["###LOCATIONDESCRIPTION###"] = $this->pi_RTEcssText($row['locationdescription']);
					$marker["###HOMEPAGE###"] = htmlspecialchars($row['homepage']);
					
					$marker["###LL_LOCATIONHP###"] = htmlspecialchars($this->pi_getLL('locationHP'));
					
					$marker["###LL_LOCATIONTITLE###"] = $this->pi_getLL('locationTitle');
					
					$marker["###LINKCLASS###"] = 'infolink';
				}else{
					$marker["###DIV_IDS###"] = '';
					
					$marker["###CLOSEBOX###"] = '';
					
					$marker["###LOCATION###"] = '';
					$marker["###STREET###"] = '';
					$marker["###ZIP###"] = '';
					$marker["###CITY###"] = '';
					$marker["###PIC###"] = '';
					
					$marker["###LOCATIONDESCRIPTION###"] = '';
					$marker["###HOMEPAGE###"] = '';
					
					$marker["###LL_LOCATIONHP###"] = '';
					
					$marker["###LL_LOCATIONTITLE###"] = '';
					
					$marker["###LINKCLASS###"] = '';
				}
				
				$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);

				/* event images */
				if($this->showImages){
					if($row['images'] != ""){
						// if more then 1 image:
						$imageList = explode(',',$row['images']);
						$imageAltTagList = explode(PHP_EOL,$row['imagesalt']);
						$imageTitleTagList = explode(PHP_EOL,$row['imagestitle']);
						$imageSubTitleTextList = explode(PHP_EOL,$row['imagessubtitle']);
						$this->eventImages = "";
						
						if($this->showAllImages){
							for($i=0;$i<count($imageList);$i++){
								$this->eventImages .= $this->getEventImage($imageList[$i],'eventpicsSingle.',$imageAltTagList[$i],$imageTitleTagList[$i]);
								$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitle">' . $imageSubTitleTextList[$i] . '</div>';
							}
						}else{
							$this->eventImages .= $this->getEventImage($imageList[0],'eventpicsSingle.',$imageAltTagList[0],$imageTitleTagList[0]);
							$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitle">' . $imageSubTitleTextList[0] . '</div>';
						}
					}else{
						$this->eventImages = "";
					}
					$marker["###IMAGES###"] = $this->eventImages;
				}else{
					$marker["###IMAGES###"] = "";
				}
				

				if(intval($this->showMorelink) == 1){
					$marker["###TEASERDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['teaserdescription']));
					// empty the others:
					$marker["###EVENTDESCRIPTION###"] = "";
				}else{
					$marker["###TEASERDESCRIPTION###"] = "";
					$marker["###EVENTDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['eventdescription']));
				}
				
				$marker["###LL_STARTTIME###"] = htmlspecialchars($this->pi_getLL('starttime'));

				if(intval($startingtime) > 0){
					$marker["###STARTTIME###"] = gmstrftime('%H:%M', $startingtime);
				}else{
					$marker["###STARTTIME###"] = '-';
				}
				$marker["###LL_PRICE###"] = htmlspecialchars($this->pi_getLL('price'));
			
				// more Link anchor
				$marker["###EVENTANCHOR###"] = 'eventanchor'.$row['uid'];
				// more Link:
				if(intval($this->showMorelink) == 1){
					$marker["###MORE_LINK###"] = '<a class="kb_eventboard_morelink" href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_morelink" >'.htmlspecialchars($this->pi_getLL('morelink')).'</a>';
				}else{
					$marker["###MORE_LINK###"] = '';
				}
				
				// Adds hook for processing of extra markers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showSingleViewMarkerHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showSingleViewMarkerHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$marker = $_procObj->showSingleViewMarker($marker, $row, $this->conf, $this);
					}
				}


				$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplEvent, $marker);
				$this->spanindex ++;
			} // end while

		
		}

		return $tmpContent;
	}	
//###########################################################################	
	
	
	
	
	
	
	
	
	
	

	
	
	/**
	 * showFrontpage Function: shows the events without category selector nor navigation buttons.
	 *
	 * @param	-
	 * @return	content
	 */
	function showFrontpage() {
		
		$ttcontentID = explode(":", $this->cObj->currentRecord);
		$ttcontentID = $ttcontentID[1];
	
		//Javascript Function
		$this->javascriptFkt = '
		<script type="text/javascript">
		/*<![CDATA[*/
		
		gSteps = '.$this->step.';
		kb_eventboard_ctime = 0;
		function showDiv'.$ttcontentID.'(infofeld){
			for(i=0;i<=gSteps;i++){
				tmpId = "text_'.$ttcontentID.'_"+i+"";
				if(document.getElementById(tmpId)){
					document.getElementById(tmpId).style.display="none";
				}
			}
			document.getElementById(infofeld).style.display="inline";
			if(kb_eventboard_ctime > 0){
				clearTimeout(kb_eventboard_ctime);
				kb_eventboard_ctime = 0;
			}
		}
		
		function hideDiv(infofeld){
		  document.getElementById(infofeld).style.display="none";
		}
		/*]]>*/
		</script>';
		
		// prepare sql statement for categories set in flexform:
		$sqlCategories = '';
		foreach($this->sel_categories as $val){
			$sqlCategories .= 'FIND_IN_SET("'.$val. '",ev.category ) OR ';
		}
		$sqlCategories = " AND ( " . substr ( $sqlCategories, 0, -3 ) . " )";

		$pagesIdList = $this->eventFolder;
		foreach($pagesIdList as $index => $root) {
			$selectedPids .= (($index == 0)?"":",").$this->getRecursiveUidList($root,$this->startingpointRecursiveLevel);
		}
		$selectedPidsList = explode(',',$selectedPids);
		$selectedPidsList = array_unique($selectedPidsList);
		
		for ($i=0; $i<count($selectedPidsList); $i++) {
		    if ($i==0) {
			$sqlpids = "(ev.pid = ".$selectedPidsList[0];
		    } else {
			$sqlpids = $sqlpids." OR ev.pid = $selectedPidsList[$i]";
		    }
		}
		$sqlpids = $sqlpids." AND ev.pid = lo.pid)";
	
	
		//Get Data
		if(count($this->sel_categories) > 0){
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
					  AND ev.hidden=0 AND '.$sqlpids.' 
					  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
					  AND ev.location = lo.uid 
					  '.$sqlCategories.'','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');	
			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
					  AND ev.hidden=0 AND '.$sqlpids.' 
					  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
					  AND ev.location = lo.uid 
					  '.$sqlCategories.' 
					  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
					  
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
					  AND ev.hidden=0 AND '.$sqlpids.' 
					  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
					  AND ev.location = lo.uid 
					  '.$sqlCategories.' 
					  AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');	
			}		  
		}else{
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
				  AND ev.hidden=0 AND '.$sqlpids.' 
				  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
				  AND ev.location = lo.uid','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');

			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
				  AND ev.hidden=0 AND '.$sqlpids.' 
				  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
				  AND ev.location = lo.uid 
				  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');

			}else{  // only new events  
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
				  AND ev.hidden=0 AND '.$sqlpids.' 
				  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
				  AND ev.location = lo.uid 
				  AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
			}
		}
		// if no records:
		if(!$res){
			return htmlspecialchars($this->pi_getLL('norecords'));
		}
		
		// Vars for templating:
		$tmpContent = "";
		$marker = array();
		$markerBase = array();
		$markerFooter = array();
		
		// Subpart Header:
		$this->tmplHeader = $this->cObj->getSubpart($this->tmpl, "###EVENTBOARD###");
		// Subpart Events:
		$this->tmplEvent = $this->cObj->getSubpart($this->tmpl, "###EVENT_FRONTPAGEVIEW###");
		
		if($this->include_css){
			$markerBase["###CSSSTYLE###"] = '<link rel="stylesheet" type="text/css" media="screen" href="typo3conf/ext/kb_eventboard/kb_eventboard.css" />';
		}else{
			$markerBase["###CSSSTYLE###"] = '';
		}
		
		if($this->showLocation){
			$markerBase["###JAVASCRIPTFKT###"] = $this->javascriptFkt;
		}else{
			$markerBase["###JAVASCRIPTFKT###"] = '';
		}
		$markerBase["###LL_HEADLINE###"] = '';
		$markerBase["###CATEGORYSELETOR###"] = '';
		
		$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplHeader, $markerBase);
		
		//Print Records
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			// even_odd style sheet class:
			$marker["###EVEN_ODD###"] =  ($this->spanindex % 2 == 0) ? " ".$this->csseven : " ". $this->cssodd;
			
			if($row['price'] == '') {
				$aktPrice = htmlspecialchars($this->pi_getLL('priceFree'));
			} else {
				$aktPrice = $row['price'].' '.htmlspecialchars($this->pi_getLL('currency'));
			}
			$marker["###PRICE###"] = $aktPrice;
			
			//Check daylight saving time -- Is that the right way???
			$startingtime = $row['startingtime'];
			$marker["###DATUM###"] = $this->cObj->stdWrap($row['datebegin'], $this->conf['showFrontpage.']['date_stdWrap.']);			
			if($row['dateend'] > 0 && $row['dateend'] != $row['datebegin']){
				$marker["###ENDDATUM###"] = '- ' . $this->cObj->stdWrap($row['dateend'], $this->conf['showFrontpage.']['date_stdWrap.']);
			}else {
				$marker["###ENDDATUM###"] = '';
			}
			$marker["###CONTENT_ID###"] = $ttcontentID;
			
			if($this->showLocation){
				$marker["###DIV_IDS###"] = 'text_'.$ttcontentID.'_'.$this->spanindex;
				
				$marker["###CLOSEBOX###"] = '<img src="typo3conf/ext/kb_eventboard/res/closebtn.gif" alt="close-button" />';
				
				$marker["###LOCATION###"] = htmlspecialchars($row['locationname']);
				$marker["###STREET###"] = htmlspecialchars($row['street']);
				$marker["###ZIP###"] = htmlspecialchars($row['zip']);
				$marker["###CITY###"] = htmlspecialchars($row['city']);
				$marker["###PIC###"] = $this->getImage($row['logo'],$row['alttext'],$row['titletext']) . '<div class="kb_eventboard_eventImageSubtitle">' . $row['subtitletext'] . '</div>';
				
				$marker["###LOCATIONDESCRIPTION###"] = $this->pi_RTEcssText($row['locationdescription']);
				$marker["###HOMEPAGE###"] = htmlspecialchars($row['homepage']);
				
				$marker["###LL_LOCATIONHP###"] = htmlspecialchars($this->pi_getLL('locationHP'));
				
				$marker["###LL_LOCATIONTITLE###"] = $this->pi_getLL('locationTitle');
				
				$marker["###LINKCLASS###"] = 'infolink';
			}else{
				$marker["###DIV_IDS###"] = '';
				
				$marker["###CLOSEBOX###"] = '';
				
				$marker["###LOCATION###"] = '';
				$marker["###STREET###"] = '';
				$marker["###ZIP###"] = '';
				$marker["###CITY###"] = '';
				$marker["###PIC###"] = '';
				
				$marker["###LOCATIONDESCRIPTION###"] = '';
				$marker["###HOMEPAGE###"] = '';
				
				$marker["###LL_LOCATIONHP###"] = '';
				
				$marker["###LL_LOCATIONTITLE###"] = '';
				
				$marker["###LINKCLASS###"] = '';
			}
			
			
			/* event images */
			if($this->showImages){
				if($row['images'] != ""){
					// if more then 1 image:
					$imageList = explode(',',$row['images']);
					$imageAltTagList = explode(PHP_EOL,$row['imagesalt']);
					$imageTitleTagList = explode(PHP_EOL,$row['imagestitle']);
					$imageSubTitleTextList = explode(PHP_EOL,$row['imagessubtitle']);
					$this->eventImages = "";
					
					if($this->showAllImages){
						for($i=0;$i<count($imageList);$i++){
							$this->eventImages .= $this->getEventImage($imageList[$i],'eventpicsFrontpage.',$imageAltTagList[$i],$imageTitleTagList[$i]);
							$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitle">' . $imageSubTitleTextList[$i] . '</div>';
						}
					}else{
						$this->eventImages .= $this->getEventImage($imageList[0],'eventpicsFrontpage.',$imageAltTagList[0],$imageTitleTagList[0]);
						$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitle">' . $imageSubTitleTextList[0] . '</div>';
					}
				}else{
					$this->eventImages = "";
				}
				$marker["###IMAGES###"] = $this->eventImages;
			}else{
				$marker["###IMAGES###"] = "";
			}

						
			if(intval($this->showMorelink) == 1){
				$marker["###TEASERDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['teaserdescription']));
				// empty the others:
				$marker["###EVENTDESCRIPTION###"] = "";
			}else{
				$marker["###TEASERDESCRIPTION###"] = "";
				$marker["###EVENTDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['eventdescription']));
			}
			
			$marker["###LL_STARTTIME###"] = htmlspecialchars($this->pi_getLL('starttime'));
			if(intval($startingtime) > 0){
				$marker["###STARTTIME###"] = gmstrftime('%H:%M', $startingtime);
			}else{
				$marker["###STARTTIME###"] = '-';
			}
			$marker["###LL_PRICE###"] = htmlspecialchars($this->pi_getLL('price'));
			
			// more Link anchor
			$marker["###EVENTANCHOR###"] = 'eventanchor'.$row['uid'];
			// more Link:
			if(intval($this->showMorelink) == 1){
				$marker["###MORE_LINK###"] = '<a class="kb_eventboard_morelink" href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_morelink" >'.htmlspecialchars($this->pi_getLL('morelink')).'</a>';
				// set Link title:
				if(intval($this->showTitleLink) == 1){
					$marker["###EVENTNAME###"] = '<a href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_titlelink" >'.htmlspecialchars($row['eventname']).'</a>';
				}else{
					$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);
				}
			}else{
				$marker["###MORE_LINK###"] = '';
				$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);
			}
			
			// Adds hook for processing of extra markers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showFrontpageMarkerHook'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showFrontpageMarkerHook'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$marker = $_procObj->showFrontpageMarker($marker, $row, $this->conf, $this);
				}
			}

			$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplEvent, $marker);
			$this->spanindex ++;
		} // end while

		return $tmpContent;
	}


	/**
	 * showContextCol Function: shows the events for presentation at context column (left or right column)
	 *
	 * @param	-
	 * @return	content
	 */
	function showContextCol() {
		
		$ttcontentID = explode(":", $this->cObj->currentRecord);
		$ttcontentID = $ttcontentID[1];
		
		// prepare sql statement for categories set in flexform:
		$sqlCategories = '';
		foreach($this->sel_categories as $val){
			$sqlCategories .= 'OR ev.category = '.$val. ' ';  
		}
		$sqlCategories = " AND ( " . substr ( $sqlCategories, 2 ) . " )";

		$pagesIdList = $this->eventFolder;
		foreach($pagesIdList as $index => $root) {
			$selectedPids .= (($index == 0)?"":",").$this->getRecursiveUidList($root,$this->startingpointRecursiveLevel);
		}
		$selectedPidsList = explode(',',$selectedPids);
		$selectedPidsList = array_unique($selectedPidsList);
		
		for ($i=0; $i<count($selectedPidsList); $i++) {
		    if ($i==0) {
			$sqlpids = "(ev.pid = ".$selectedPidsList[0];
		    } else {
			$sqlpids = $sqlpids." OR ev.pid = $selectedPidsList[$i]";
		    }
		}
		$sqlpids = $sqlpids." AND ev.pid = lo.pid)";
		
		//Get Data
		if($sqlCategories != ""){
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
							      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
							      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
							      AND ev.location = lo.uid 
								  '.$sqlCategories.'','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
							      AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
							      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
							      AND ev.location = lo.uid 
								  '.$sqlCategories.' 
							      AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
								  
			}else{  // only new events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev LEFT JOIN tx_kbeventboard_locations lo ON ev.location = lo.uid LEFT JOIN tx_kbeventboard_category ca ON ev.category = ca.uid','ev.deleted=0 
							      AND ev.hidden=0 AND '.$sqlpids.' 
							      AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
							      AND ev.location = lo.uid 
								  '.$sqlCategories.' 
							      AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
			}
		}else{
			if($this->show_time_mode == 1){ // all events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								  AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
								  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								  AND ev.location = lo.uid','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
			}else if($this->show_time_mode == 2){ // only old events
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								  AND ev.hidden=0 '.$this->eventFolderSQLPart.' 
								  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								  AND ev.location = lo.uid 
								  AND (ev.dateend <= '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
			}else{  // only new events

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*,ev.uid','tx_kbeventboard_events ev, tx_kbeventboard_locations lo','ev.deleted=0 
								  AND ev.hidden=0 AND '.$sqlpids.' 
								  AND (ev.fe_group = 0 OR ev.fe_group IN ('.implode(',',$this->aktUserGroup).')) 
								  AND ev.location = lo.uid 
								  AND ((ev.datebegin + 86400 + ev.delaytime) > '.$this->today.' OR ev.dateend > '.$this->yesterday.')','','ev.datebegin '. $this->ascDesc . '','0,'.$this->step.'');
			}
		}
		// if no records:
		if(!$res){
			return htmlspecialchars($this->pi_getLL('norecords'));
		}
	
		// Vars for templating:
		$tmpContent = "";
		$marker = array();
		$markerBase = array();
		$markerFooter = array();
		

		// Subpart Header:
		$this->tmplHeader = $this->cObj->getSubpart($this->tmpl, "###EVENTBOARD###");
		// Subpart Events:
		$this->tmplEvent = $this->cObj->getSubpart($this->tmpl, "###EVENT_CONTEXTCOLVIEW###");
		
		if($this->include_css){
			$markerBase["###CSSSTYLE###"] = '<link rel="stylesheet" type="text/css" media="screen" href="typo3conf/ext/kb_eventboard/kb_eventboard.css" />';
		}else{
			$markerBase["###CSSSTYLE###"] = '';
		}
		
		$markerBase["###JAVASCRIPTFKT###"] = '';
		$markerBase["###LL_HEADLINE###"] = '';
		$markerBase["###CATEGORYSELETOR###"] = '';
		
		$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplHeader, $markerBase);
		
		//Print Records
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	
		
			// even_odd style sheet class:
			$marker["###EVEN_ODD###"] =  ($this->spanindex % 2 == 0) ? " ".$this->csseven : " ". $this->cssodd;
			
			if($row['price'] == '') {
				$aktPrice = htmlspecialchars($this->pi_getLL('priceFree'));
			} else {
				$aktPrice = $row['price'].' '.htmlspecialchars($this->pi_getLL('currency'));
			}
			$marker["###PRICE###"] = $aktPrice;
			
			//Check daylight saving time -- Is that the right way???
			$startingtime = $row['startingtime'];
			$marker["###DATUM###"] = $this->cObj->stdWrap($row['datebegin'], $this->conf['showContextCol.']['date_stdWrap.']);			
			if($row['dateend'] > 0 && $row['dateend'] != $row['datebegin']){
				$marker["###ENDDATUM###"] = '- ' . $this->cObj->stdWrap($row['dateend'], $this->conf['showContextCol.']['date_stdWrap.']);
			}else {
				$marker["###ENDDATUM###"] = '';
			}
			$marker["###CONTENT_ID###"] = $ttcontentID;
			$marker["###DIV_IDS###"] = 'text_'.$ttcontentID.'_'.$this->spanindex;
			
			$marker["###CLOSEBOX###"] = '<img src="typo3conf/ext/kb_eventboard/res/closebtn.gif" alt="close-button" />';
			
			$marker["###LOCATION###"] = htmlspecialchars($row['locationname']);
			$marker["###STREET###"] = htmlspecialchars($row['street']);
			$marker["###ZIP###"] = htmlspecialchars($row['zip']);
			$marker["###CITY###"] = htmlspecialchars($row['city']);
			$marker["###PIC###"] = $this->getImage($row['logo'],$row['alttext'],$row['titletext']) . '<div class="kb_eventboard_eventImageSubtitle">' . $row['subtitletext'] . '</div>';
			
			$marker["###LOCATIONDESCRIPTION###"] = $this->pi_RTEcssText($row['locationdescription']);
			$marker["###HOMEPAGE###"] = htmlspecialchars($row['homepage']);
			
			$marker["###LL_LOCATIONHP###"] = htmlspecialchars($this->pi_getLL('locationHP'));
						
			if(intval($this->showMorelink) == 1){
				$marker["###TEASERDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['teaserdescription']));
			}else{
				$marker["###TEASERDESCRIPTION###"] = $this->formatStr($this->pi_RTEcssText($row['eventdescription']));
			}
			$marker["###LL_STARTTIME###"] = htmlspecialchars($this->pi_getLL('starttime'));
			if(intval($startingtime) > 0){
				$marker["###STARTTIME###"] = gmstrftime('%H:%M', $startingtime);
			}else{
				$marker["###STARTTIME###"] = '-';
			}
			$marker["###LL_PRICE###"] = htmlspecialchars($this->pi_getLL('price'));
			
			/* event images */
			if($this->showImages){
				if($row['images'] != ""){
					// if more then 1 image:
					$imageList = explode(',',$row['images']);
					$imageAltTagList = explode(PHP_EOL,$row['imagesalt']);
					$imageTitleTagList = explode(PHP_EOL,$row['imagestitle']);
					$imageSubTitleTextList = explode(PHP_EOL,$row['imagessubtitle']);
					
					$this->eventImages = "";
					
					if($this->showAllImages){
						foreach($imageList as $img){
							$this->eventImages .= $this->getEventImage($img,'eventpicsContext.');
						}
						for($i=0;$i<count($imageList);$i++){
							$this->eventImages .= $this->getEventImage($imageList[$i],'eventpicsContext.',$imageAltTagList[$i],$imageTitleTagList[$i]);
							$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitleCenter">' . $imageSubTitleTextList[$i] . '</div>';
						}
					}else{
						$this->eventImages .= $this->getEventImage($imageList[0],'eventpicsContext.',$imageAltTagList[0],$imageTitleTagList[0]);
						$this->eventImages .= '<div class="kb_eventboard_eventImageSubtitleCenter">' . $imageSubTitleTextList[0] . '</div>';
					}
				}else{
					$this->eventImages = "";
				}
				$marker["###IMAGES###"] = $this->eventImages;
			}else{
				$marker["###IMAGES###"] = "";
			}

		
			// more Link:
			if(intval($this->showMorelink) == 1){
				$marker["###MORE_LINK###"] = '<a class="kb_eventbox_morelink" href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_morelink" >'.htmlspecialchars($this->pi_getLL('morelink')).'</a>';
				// set Link title:
				if(intval($this->showTitleLink) == 1){
					$marker["###EVENTNAME###"] = '<a href="'. $this->pi_linkTP_keepPIvars_url(Array('evt' => $row['uid']),1,0,$this->morelinkId). '" class="kb_eventboard_contextcol-titlelink" >'.htmlspecialchars($row['eventname']).'</a>';
				}else{
					$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);
				}
			}else{
				$marker["###MORE_LINK###"] = '';
				$marker["###EVENTNAME###"] = htmlspecialchars($row['eventname']);
			}
			
			// Adds hook for processing of extra markers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showContextColMarkerHook'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']['showContextColMarkerHook'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$marker = $_procObj->showContextColMarker($marker, $row, $this->conf, $this);
				}
			}
			
			$tmpContent .= $this->cObj->substituteMarkerArrayCached($this->tmplEvent, $marker);
			$this->spanindex ++;
		} // end while

		return $tmpContent;
	}	
	
	/* render location image as defined in TS */
	function getImage($imageName,$alttag,$titletag){
		//Get image-config from typoscript
		$imageConfig         = $this->conf['thumb.'];
		//Set image Path
		$imageConfig['file'] = 'uploads/tx_kbeventboard/'.$imageName;
		$imageConfig['altText'] = $alttag;
		$imageConfig['titleText'] = $titletag;
		return $this->cObj->IMAGE($imageConfig);
	}
	/* render event images as defined in TS */
	function getEventImage($imageName,$confStyle,$alttag,$titletag){
		//Get image-config from typoscript
		$imageConfig         = $this->conf[$confStyle];
		
		//Set image Path
		$imageConfig['file'] = 'uploads/tx_kbeventboard/'.$imageName;
		$imageConfig['altText'] = $alttag;
		$imageConfig['titleText'] = $titletag;
		return $this->cObj->IMAGE($imageConfig);
	}
	
	function formatStr($str) {
		if (is_array($this->conf["general_stdWrap."])) {
		    $str = $this->cObj->stdWrap($str,$this->conf["general_stdWrap."]);
		  }
		return $str;
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_eventboard/pi1/class.tx_kbeventboard_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_eventboard/pi1/class.tx_kbeventboard_pi1.php']);
}

?>
