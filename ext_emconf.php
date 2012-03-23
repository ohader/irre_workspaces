<?php

########################################################################
# Extension Manager/Repository config file for ext "irre_workspaces".
#
# Auto generated 12-03-2012 16:21
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
	'dependencies' => 'workspaces,version',
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
	'version' => '0.3.0-dev',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
			'workspaces' => '4.5.0-0.0.0',
			'version' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:32:{s:10:"README.txt";s:4:"ee2d";s:16:"ext_autoload.php";s:4:"cff9";s:12:"ext_icon.gif";s:4:"b4e6";s:17:"ext_localconf.php";s:4:"a1b9";s:14:"ext_tables.php";s:4:"fb94";s:14:"ext_tables.sql";s:4:"47de";s:16:"locallang_db.xml";s:4:"c71f";s:31:"Classes/Domain/Model/Record.php";s:4:"5c22";s:35:"Classes/ExtDirect/ActionHandler.php";s:4:"dcd0";s:37:"Classes/ExtDirect/ParameterRecord.php";s:4:"e2b6";s:36:"Classes/Service/BehaviourService.php";s:4:"b67f";s:37:"Classes/Service/ComparisonService.php";s:4:"70e3";s:37:"Classes/Service/DependencyService.php";s:4:"2eb0";s:37:"Classes/Service/SanitazionService.php";s:4:"d0e8";s:34:"Classes/Service/TceMainService.php";s:4:"36de";s:33:"Classes/XClasses/PageRenderer.php";s:4:"6327";s:28:"Classes/XClasses/TceMain.php";s:4:"ef5b";s:62:"Classes/XClasses/Tx_Workspaces_Controller_ReviewController.php";s:4:"185f";s:58:"Classes/XClasses/Tx_Workspaces_ExtDirect_ActionHandler.php";s:4:"2589";s:51:"Classes/XClasses/Tx_Workspaces_Service_GridData.php";s:4:"2d48";s:34:"Configuration/TCA/SysWorkspace.php";s:4:"86fb";s:39:"Configuration/TCA/SysWorkspaceStage.php";s:4:"cbe3";s:38:"Resources/Public/JavaScript/Actions.js";s:4:"6eb3";s:41:"Resources/Public/JavaScript/Controller.js";s:4:"aa23";s:47:"Resources/Public/JavaScript/Override/Actions.js";s:4:"b2b7";s:49:"Resources/Public/JavaScript/Override/Component.js";s:4:"e7a7";s:53:"Resources/Public/JavaScript/Override/Configuration.js";s:4:"5c75";s:44:"Resources/Public/JavaScript/Override/Grid.js";s:4:"2071";s:47:"Resources/Public/JavaScript/Override/Toolbar.js";s:4:"d3ed";s:51:"Resources/Public/JavaScript/Plugin/MultiGrouping.js";s:4:"2374";s:38:"Resources/Public/Stylesheet/Module.css";s:4:"b795";s:44:"Tests/Tx_Workspaces_Service_GridDataTest.php";s:4:"7510";}',
	'suggests' => array(
	),
);

?>