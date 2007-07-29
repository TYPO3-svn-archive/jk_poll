<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_extMgm::allowTableOnStandardPages("tx_jkpoll_poll");

$TCA["tx_jkpoll_poll"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:jk_poll/locallang_db.php:tx_jkpoll_poll",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jkpoll_poll.gif",
		
		"prependAtCopy" => "LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy",
		"copyAfterDuplFields" => "sys_language_uid",
		"useColumnsForDefaultValues" => "sys_language_uid",
		"transOrigPointerField" => "l18n_parent",
		"transOrigDiffSourceField" => "l18n_diffsource",
		"languageField" => "sys_language_uid",

	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, image, question, votes, answers, colors, valid_till",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(Array("LLL:EXT:jk_poll/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Poll");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:jk_poll/flexform_ds.xml');

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_jkpoll_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_jkpoll_pi1_wizicon.php";
?>