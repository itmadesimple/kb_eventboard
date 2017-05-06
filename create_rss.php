<?php

/**
 * this class creates our RSS-Feed
 *
 * @author		Klaus Biedermann (klaus.biedermann@gmx.de)
 */
 

class user_createRSS {

	var $cObj;// The backReference to the mother cObj object set at call time
	var $eventRecordPid; // Page Id of event data collection

	/**
	 * main function, does everything you need... ;-)
	 *
	 * @param	$content	content...
	 * @param	$conf		"variables"
	 */
	function main($content, $conf) {

		// some basic stuff:
		$ts = time();
		$base_url = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'];
		$this->eventRecordPid = $conf['eventRecordPid'];
		
		$content = '<?xml version="1.0" encoding="UTF-8" ?>
		<rss version="2.0" >
		<channel>';
		
		$content .= '<title>RSS Feed YOURWEBSITE.TLD</title>
					<link>http://www.YOURWEBSITE.TLD</link>
					<description>Alle Veranstaltungen im Ueberblick</description>';
		$content .= '<language>de-de</language>
					<copyright>'. date('Y') .' by YOURWEBSITE.TLD</copyright>';
		
		$content .= '<image>
			<title>Aktuelle Veranstaltungen</title>
			<url>http://YOURWEBSITE.TLD/typo3conf/ext/kb_eventboard/res/rss_icon_16x16.gif</url>
			<link>http://www.YOURWEBSITE.TLD/</link>
			<width>16</width>
			<height>16</height>
			<description>Aktuelle Veranstaltungen</description>
		</image>';

				
				$select_fields = '*';
				$from_table = 'tx_kbeventboard_events'; // table of your extension
				$where = "pid = ".$this->eventRecordPid." AND deleted = 0 AND hidden = 0 AND datebegin > ".$ts."";
	
				$group = '';
				$order = 'datebegin ASC';
				$limit = '40';
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where, $group, $order, $limit);
				
				$sql = $GLOBALS['TYPO3_DB']->SELECTquery($select_fields, $from_table, $where, $group, $order, $limit);


				// now put in all the items:
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){

					$pubDate = date('D, d M Y H:i:s O', $row["datebegin"]); // timestamp in the correct format for the rss feed
					$link_to_detailpage = $base_url . 'index.php?id='.$conf["singlePid"].'&amp;tx_kbeventboard_pi1[evt]='.$row["uid"]; // link to the page with the detail view
					$description = substr(strip_tags($row["eventdescription"]), 0, $conf["maxChars"]) . ' [...]';
					$eventTitle = strip_tags($row["eventname"]);
					
					$filter = array(
								'&nbsp;'=>'', '&quote;'=>'"', '& ' => ' +'
					);

					foreach ($filter as $from => $to) {
						$description = str_replace($from, $to, $description);
						$eventTitle = str_replace($from, $to, $eventTitle);
					}
					

					$content .= '<item>' . "\n";
						$content .= '<title>'.date('d-m-Y',$row["datebegin"]).': '.$eventTitle.'</title>' . "\n";
						$content .= '<link>'.$link_to_detailpage.'</link>' . "\n";
						$content .= '<description>'.$description.'</description>' . "\n";
						$content .= '<guid>'.$link_to_detailpage.'</guid>' . "\n";
						$content .= '<pubdate>'.$pubDate.'</pubdate>' . "\n";
					$content .= '</item>';
				}

			$content .= '</channel>';
		$content .= '</rss>';

		return $content;
	}

}
?>