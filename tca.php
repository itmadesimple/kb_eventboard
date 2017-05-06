<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_kbeventboard_locations"] = Array (
	"ctrl" => $TCA["tx_kbeventboard_locations"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "locationname,street,zip,city,logo,locationdescription,homepage"
	),
	"feInterface" => $TCA["tx_kbeventboard_locations"]["feInterface"],
	"columns" => Array (
		"locationname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.locationname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"street" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.street",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"zip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.zip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"city" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.city",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"logo" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.logo",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "gif,png,jpeg,jpg",	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_kbeventboard",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"alttext" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.alttext",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"titletext" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.titletext",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"subtitletext" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.subtitletext",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"locationdescription" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"homepage" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_locations.homepage",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "locationname;;;;1-1-1, street, zip, city, logo, alttext, titletext, subtitletext, locationdescription, homepage")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_kbeventboard_category"] = Array (
	"ctrl" => $TCA["tx_kbeventboard_category"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "category,categorydescription"
	),
	"feInterface" => $TCA["tx_kbeventboard_category"]["feInterface"],
	"columns" => Array (
		"category" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_category.category",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"categorydescription" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_category.description",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "category;;;;1-1-1, categorydescription")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_kbeventboard_events"] = Array (
	"ctrl" => $TCA["tx_kbeventboard_events"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fe_group,eventname,datebegin,dateend,location,category,startingtime,delaytime,price,teaserimages,teaserdescription,images,eventdescription"
	),
	"feInterface" => $TCA["tx_kbeventboard_events"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.fe_group",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.xml:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.xml:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"eventname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.eventname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"datebegin" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.datebegin",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"dateend" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.dateend",		
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"location" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.location",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_kbeventboard_locations",	
				"foreign_table_where" => "AND tx_kbeventboard_locations.pid=###CURRENT_PID### ORDER BY tx_kbeventboard_locations.uid",	
				"size" => 1,	
				"minitems" => 1,
				"maxitems" => 1,
				"eval" => 'required',
			)
		),
		"category" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.category",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_kbeventboard_category",	
				"foreign_table_where" => "AND tx_kbeventboard_category.pid=###CURRENT_PID### ORDER BY tx_kbeventboard_category.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
		"startingtime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.startingtime",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				"eval" => "time",
			)
		),
		"delaytime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.delaytime",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				"eval" => "time",
			)
		),
		"price" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.price",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"teaserimages" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.teaserimages",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => "gif,png,jpeg,jpg",    
                "max_size" => 500,    
                "uploadfolder" => "uploads/tx_kbeventboard",
                "show_thumbs" => 1,    
                "size" => 4,    
                "minitems" => 0,
                "maxitems" => 10,
            )
        ),
		"teaserimagesalt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.teaserimagesAlttext",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"teaserimagestitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.teaserimagesTitletext",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"teaserimagessubtitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.teaserimagesSubTitletext",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"teaserdescription" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.teaserdescription",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"images" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.images",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => "gif,png,jpeg,jpg",    
                "max_size" => 500,    
                "uploadfolder" => "uploads/tx_kbeventboard",
                "show_thumbs" => 1,    
                "size" => 4,    
                "minitems" => 0,
                "maxitems" => 10,
            )
        ),
		"imagesalt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.imagesAlttext",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"imagestitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.imagesTitletext",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"imagessubtitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.imagesSubTitletext",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"eventdescription" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:kb_eventboard/locallang_db.xml:tx_kbeventboard_events.eventdescription",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, eventname, datebegin, dateend, location, category, startingtime, delaytime, price, teaserimages, teaserimagesalt, teaserimagestitle, teaserimagessubtitle, teaserdescription;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=css|imgpath=uploads/tx_kbeventboard/], images, imagesalt, imagestitle, imagessubtitle, eventdescription;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=css|imgpath=uploads/tx_kbeventboard/]")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "fe_group")
	)
);
?>