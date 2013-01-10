<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "irre_workspaces".
 *
 * Auto generated 10-01-2013 14:41
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

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
	'version' => '0.4.4-dev',
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
	'_md5_values_when_last_written' => 'a:70:{s:16:"ext_autoload.php";s:4:"5bf2";s:21:"ext_conf_template.txt";s:4:"eda1";s:12:"ext_icon.gif";s:4:"b4e6";s:17:"ext_localconf.php";s:4:"59e3";s:14:"ext_tables.php";s:4:"19fc";s:14:"ext_tables.sql";s:4:"47de";s:16:"locallang_db.xml";s:4:"c71f";s:10:"README.txt";s:4:"6d1c";s:31:"Classes/Domain/Model/Record.php";s:4:"5c22";s:55:"Classes/Domain/Model/Dependency/IncompleteStructure.php";s:4:"4d44";s:50:"Classes/Domain/Model/Node/AbstractChildrenNode.php";s:4:"9c7d";s:49:"Classes/Domain/Model/Node/AbstractContentNode.php";s:4:"859b";s:41:"Classes/Domain/Model/Node/CommentNode.php";s:4:"274e";s:50:"Classes/Domain/Model/Node/HasChildrenInterface.php";s:4:"e36f";s:49:"Classes/Domain/Model/Node/HasContextInterface.php";s:4:"c3a0";s:48:"Classes/Domain/Model/Node/HasParentInterface.php";s:4:"f1e2";s:38:"Classes/Domain/Model/Node/HtmlNode.php";s:4:"fa48";s:44:"Classes/Domain/Model/Node/NodeCollection.php";s:4:"c9f9";s:38:"Classes/Domain/Model/Node/RootNode.php";s:4:"8c21";s:38:"Classes/Domain/Model/Node/TextNode.php";s:4:"8e89";s:35:"Classes/ExtDirect/ActionHandler.php";s:4:"63dc";s:37:"Classes/ExtDirect/ParameterRecord.php";s:4:"e2b6";s:48:"Classes/Hooks/BackendControllerResourcesHook.php";s:4:"43aa";s:37:"Classes/Hooks/ExtDirectServerHook.php";s:4:"797d";s:43:"Classes/Hooks/PageTreeVisualizationHook.php";s:4:"822b";s:29:"Classes/Hooks/PreviewHook.php";s:4:"6a0a";s:47:"Classes/Hooks/ReviewControllerResourcesHook.php";s:4:"2ca5";s:37:"Classes/Hooks/ValueProcessingHook.php";s:4:"f407";s:49:"Classes/Renderer/Notification/MessageRenderer.php";s:4:"9475";s:36:"Classes/Service/BehaviourService.php";s:4:"b67f";s:37:"Classes/Service/ComparisonService.php";s:4:"70e3";s:40:"Classes/Service/ConfigurationService.php";s:4:"0120";s:35:"Classes/Service/RedirectService.php";s:4:"b205";s:37:"Classes/Service/SanitazionService.php";s:4:"d0e8";s:34:"Classes/Service/TceMainService.php";s:4:"d1b6";s:48:"Classes/Service/Action/AbstractActionService.php";s:4:"7b8f";s:51:"Classes/Service/Action/ChangeStageActionService.php";s:4:"d702";s:54:"Classes/Service/Action/FlushWorkspaceActionService.php";s:4:"37b1";s:56:"Classes/Service/Action/PublishWorkspaceActionService.php";s:4:"430e";s:56:"Classes/Service/Dependency/AbstractDependencyService.php";s:4:"c106";s:58:"Classes/Service/Dependency/CollectionDependencyService.php";s:4:"10d8";s:53:"Classes/Service/Difference/AlternativeCoreService.php";s:4:"aa08";s:42:"Classes/Service/Field/DeviationService.php";s:4:"3849";s:38:"Classes/Service/Node/ParserService.php";s:4:"9a4b";s:43:"Classes/Service/Record/DeviationService.php";s:4:"0e3d";s:34:"Classes/XClasses/T3lib_TceMain.php";s:4:"ef5b";s:39:"Classes/XClasses/Tx_Version_TceMain.php";s:4:"4a8b";s:50:"Classes/XClasses/Tx_Version_TceMain_CommandMap.php";s:4:"3fe3";s:58:"Classes/XClasses/Tx_Workspaces_ExtDirect_ActionHandler.php";s:4:"2589";s:51:"Classes/XClasses/Tx_Workspaces_ExtDirect_Server.php";s:4:"fdec";s:51:"Classes/XClasses/Tx_Workspaces_Service_GridData.php";s:4:"9485";s:34:"Configuration/TCA/SysWorkspace.php";s:4:"86fb";s:39:"Configuration/TCA/SysWorkspaceStage.php";s:4:"cbe3";s:52:"Resources/Private/Templates/Notification/Message.txt";s:4:"3c34";s:38:"Resources/Public/JavaScript/Actions.js";s:4:"6eb3";s:41:"Resources/Public/JavaScript/Controller.js";s:4:"04a4";s:37:"Resources/Public/JavaScript/Helper.js";s:4:"93cf";s:43:"Resources/Public/JavaScript/Backend/Tree.js";s:4:"6587";s:47:"Resources/Public/JavaScript/Override/Actions.js";s:4:"b2b7";s:49:"Resources/Public/JavaScript/Override/Component.js";s:4:"d094";s:53:"Resources/Public/JavaScript/Override/Configuration.js";s:4:"5c75";s:44:"Resources/Public/JavaScript/Override/Grid.js";s:4:"a4b3";s:47:"Resources/Public/JavaScript/Override/Toolbar.js";s:4:"d3ed";s:51:"Resources/Public/JavaScript/Plugin/MultiGrouping.js";s:4:"5a9d";s:38:"Resources/Public/Stylesheet/Module.css";s:4:"44b3";s:32:"Tests/Fixture/TceMainFixture.php";s:4:"45be";s:66:"Tests/Tx_IrreWorkspaces/Classes/Domain/Model/Node/RootNodeTest.php";s:4:"f55c";s:66:"Tests/Tx_IrreWorkspaces/Classes/Service/Node/ParserServiceTest.php";s:4:"d301";s:43:"Tests/Tx_Version/Tx_Version_TceMainTest.php";s:4:"15bc";s:52:"Tests/Tx_Workspaces/Classes/Service/GridDataTest.php";s:4:"bb05";}',
	'suggests' => array(
	),
);

?>