<?php

########################################################################
# Extension Manager/Repository config file for ext "irre_workspaces".
#
# Auto generated 24-02-2012 10:23
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'IRRE Workspaces',
	'description' => 'Special handling for IRRE elements on Workspaces',
	'category' => 'be',
	'author' => 'Oliver Hader',
	'author_email' => 'oliver.hader@typo3.org',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:23:{s:10:"README.txt";s:4:"ee2d";s:16:"ext_autoload.php";s:4:"dd9e";s:12:"ext_icon.gif";s:4:"b4e6";s:17:"ext_localconf.php";s:4:"3089";s:14:"ext_tables.php";s:4:"461e";s:35:"Classes/ExtDirect/ActionHandler.php";s:4:"dcd0";s:37:"Classes/ExtDirect/ParameterRecord.php";s:4:"e2b6";s:37:"Classes/Service/DependencyService.php";s:4:"2eb0";s:37:"Classes/Service/SanitazionService.php";s:4:"8102";s:34:"Classes/Service/TceMainService.php";s:4:"74cd";s:33:"Classes/XClasses/PageRenderer.php";s:4:"6327";s:28:"Classes/XClasses/TceMain.php";s:4:"ef5b";s:62:"Classes/XClasses/Tx_Workspaces_Controller_ReviewController.php";s:4:"884f";s:51:"Classes/XClasses/Tx_Workspaces_Service_GridData.php";s:4:"2d10";s:38:"Resources/Public/JavaScript/Actions.js";s:4:"6eb3";s:41:"Resources/Public/JavaScript/Controller.js";s:4:"aa23";s:49:"Resources/Public/JavaScript/Override/Component.js";s:4:"b39a";s:53:"Resources/Public/JavaScript/Override/Configuration.js";s:4:"5c75";s:44:"Resources/Public/JavaScript/Override/Grid.js";s:4:"d43c";s:47:"Resources/Public/JavaScript/Override/Toolbar.js";s:4:"5521";s:51:"Resources/Public/JavaScript/Plugin/MultiGrouping.js";s:4:"0604";s:38:"Resources/Public/Stylesheet/Module.css";s:4:"d0b4";s:44:"Tests/Tx_Workspaces_Service_GridDataTest.php";s:4:"7510";}',
	'suggests' => array(
	),
);

?>