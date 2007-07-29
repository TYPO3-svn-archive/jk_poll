<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_jkpoll_poll"] = Array (
	"ctrl" => $TCA["tx_jkpoll_poll"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,image,question,votes,answers,colors"
	),
	"feInterface" => $TCA["tx_jkpoll_poll"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"l10n_mode" => "mergeIfNotBlank",
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"l10n_mode" => "mergeIfNotBlank",
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"l10n_mode" => "mergeIfNotBlank",
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,
			"l10n_mode" => "mergeIfNotBlank",
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"title" => Array (		
			"exclude" => 1,
			"l10n_mode" => "prefixLangTitle",
			"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"image" => Array (		
			"exclude" => 1,
			"l10n_mode" => "exclude",		
			"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_jkpoll",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"question" => Array (		
			"exclude" => 1,
			"l10n_mode" => "prefixLangTitle",
			"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.question",		
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
		"votes" => Array (		
			"exclude" => 1,
			"l10n_mode" => "exclude",
			"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.votes",		
			"config" => Array (
				"type" => "none",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"answers" => Array (		
			"exclude" => 1,
			"l10n_mode" => "prefixLangTitle",
			"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.answers",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"colors" => Array (		
			"exclude" => 1,
			"l10n_mode" => "mergeIfNotBlank",
			"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.colors",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"valid_till" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "mergeIfNotBlank",
        	"label" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll.valid_till",        
        	"config" => Array (
	            "type" => "input",
	            "size" => "8",
	            "max" => "20",
	            "eval" => "date",
	            "checkbox" => "0",
	            "default" => "0"
        	),
        ),
        
        
        "title_tag" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "prefixLangTitle",
        	"label" => "LLL:EXT:jk_poll/locallang_db.xml:tx_jkpoll_poll.title_tag",        
        	"config" => Array (
            	"type" => "text",
            	"cols" => "30",    
            	"rows" => "2",
        	)
    	),
    	"alternative_tag" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "prefixLangTitle",
        	"label" => "LLL:EXT:jk_poll/locallang_db.xml:tx_jkpoll_poll.alternative_tag",        
        	"config" => Array (
            	"type" => "text",
           		"cols" => "30",    
            	"rows" => "2",
        	)
    	),
    	"width" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "exclude",
        	"label" => "LLL:EXT:jk_poll/locallang_db.xml:tx_jkpoll_poll.width",        
        	"config" => Array (
            	"type" => "input",
            	"size" => "4",
            	"max" => "4",
            	"eval" => "int",
            	"checkbox" => "0",
            	"range" => Array (
                	"upper" => "1000",
                	"lower" => "10"
            	),
            	"default" => 0
        	)
    	),
    	"height" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "exclude",
        	"label" => "LLL:EXT:jk_poll/locallang_db.xml:tx_jkpoll_poll.height",        
        	"config" => Array (
            	"type" => "input",
            	"size" => "4",
            	"max" => "4",
            	"eval" => "int",
            	"checkbox" => "0",
            	"range" => Array (
                	"upper" => "1000",
                	"lower" => "10"
            	),
            	"default" => 0
        	)
    	),
    	"link" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "mergeIfNotBlank",
        	"label" => "LLL:EXT:jk_poll/locallang_db.xml:tx_jkpoll_poll.link",        
        	"config" => Array (
            	"type" => "input",
            	"size" => "15",
            	"max" => "255",
            	"checkbox" => "",
            	"eval" => "trim",
            	"wizards" => Array(
                	"_PADDING" => 2,
                	"link" => Array(
                    	"type" => "popup",
                    	"title" => "Link",
                    	"icon" => "link_popup.gif",
                    	"script" => "browse_links.php?mode=wizard",
                    	"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                	)
            	)
        	)
    	),
    	"clickenlarge" => Array (        
        	"exclude" => 1,
        	"l10n_mode" => "exclude",
        	"label" => "LLL:EXT:jk_poll/locallang_db.xml:tx_jkpoll_poll.clickenlarge",        
        	"config" => Array (
            	"type" => "check",
        	)
    	),
    	
    	
    	

		'sys_language_uid' => Array(
    		'exclude' => 1,
    		'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
    		'config' => Array(
     			'type' => 'select',
     			'foreign_table' => 'sys_language',
     			'foreign_table_where' => 'ORDER BY sys_language.title',
     			'items' => Array(
      				Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
      				Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
     			)
    		)
   		),
   		'l18n_parent' => Array(
    		'displayCond' => 'FIELD:sys_language_uid:>:0',
    		'exclude' => 1,
    		'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
    		'config' => Array(
     			'type' => 'select',
     			'items' => Array(
      				Array('', 0),
     			),
    			'foreign_table' => 'tx_jkpoll_poll',
     			'foreign_table_where' => 'AND tx_jkpoll_poll.uid=###REC_FIELD_l18n_parent### AND tx_jkpoll_poll.sys_language_uid IN (-1,0)',
     			'wizards' => Array(
      				'_PADDING' => 2,
      				'_VERTICAL' => 1,
      				'edit' => Array(
       					'type' => 'popup',
       					'title' => 'edit default language version of this record ',
       					'script' => 'wizard_edit.php',
       					'popup_onlyOpenIfSelected' => 1,
       					'icon' => 'edit2.gif',
       					'JSopenParams' => 'height=600,width=700,status=0,menubar=0,scrollbars=1,resizable=1',
      				)
     			)
    		)
   		),
   		'l18n_diffsource' => Array(
    		'config' => Array(
     			'type'=>'passthrough'
    		)
   		),
        
        
        
        
        
	),
	"types" => Array (
//		"0" => Array("showitem" => "hidden;;1;;1-1-1,valid_till, title;;;;2-2-2, image;;;;3-3-3, question;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], answers")
		"0" => Array("showitem" => "hidden,valid_till, title;;;;1-1-1,question;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts],votes,answers,colors, image;;;;2-2-2,title_tag,alternative_tag,width,height,link,clickenlarge")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);
?>