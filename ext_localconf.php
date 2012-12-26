<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

require_once t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Service/ConfigurationService.php';

// Modify the bahaviour of the workspace module

// Hook to add additional stylesheet and JavaScript resources to Workspaces Module:
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ReviewControllerResourcesHook.php:Tx_IrreWorkspaces_Hooks_ReviewControllerResourcesHook->renderPreProcess';

// Modify the bahaviour of the workspace module

// Hook to modify the differences view and exclude e.g. l10n_diffsource fields:
if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableRecordDetailReduction()) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['workspaces']['modifyDifferenceArray'][$_EXTKEY] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/ExtDirectServerHook.php:Tx_IrreWorkspaces_Hooks_ExtDirectServerHook';
}

// Hook to pre- and post-process values (required for FlexForm rendering)
if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableFlexFormRendering()) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['workspaces']['modifyDifferenceArray'][$_EXTKEY] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/ExtDirectServerHook.php:Tx_IrreWorkspaces_Hooks_ExtDirectServerHook';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue'][$_EXTKEY] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook->preProcess';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][$_EXTKEY] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook->postProcess';
}

// Hooks to visualize current target page if editing a workspace element
if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnablePageTreeUpdateOnEditing()) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/alt_doc.php']['makeEditForm_accessCheck'][$_EXTKEY] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/PageTreeVisualizationHook.php:Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook->handleEditing';
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['updateSignalHook']['tx_irreworkspaces::updateEditing'] =
		'EXT:' . $_EXTKEY . '/Classes/Hooks/PageTreeVisualizationHook.php:Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook->updateEditing';
}

// Hook to store original request URL during login
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] =
	'EXT:' . $_EXTKEY . '/Classes/Service/RedirectService.php:Tx_IrreWorkspaces_Service_RedirectService->handle';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][$_EXTKEY] =
	'EXT:' . $_EXTKEY . '/Classes/Service/RedirectService.php:Tx_IrreWorkspaces_Service_RedirectService->fetch';

// Hook to render preview URL for non page-related records (e.g. plugin records)
$TYPO3_CONF_VARS['SC_OPTIONS']['workspaces']['viewSingleRecord'] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/PreviewHook.php:Tx_IrreWorkspaces_Hooks_PreviewHook->getSingleRecordLink';

// Basically the XLCASSes are required to inject behaviour and JavaScript code

// @todo: Might use signal-dispatcher for TYPO3 4.7, see http://forge.typo3.org/issues/35166

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/GridData.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_Service_GridData.php';

// @todo: Might use signal-dispatcher for TYPO3 4.7, see http://forge.typo3.org/issues/35175

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/ExtDirect/ActionHandler.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_ExtDirect_ActionHandler.php';

// XClass to fetch all nodes of the page-tree at once
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/tree/pagetree/extdirect/class.t3lib_tree_pagetree_extdirect_tree.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/T3lib_Tree_PageTree_ExtDirect_Tree.php';

// XClass to enable alternative notification rendering
if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableAlternativeNotification()) {
	$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/version/class.tx_version_tcemain.php'] =
		t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Version_TceMain.php';
}

/*
DISABLED - Since the workspace module does not show unmodified elements,
there is no need to manipulate data structures (without changes) on editing

// @todo: Might use processDatamap_beforeStart for TYPO3 4.7, see http://forge.typo3.org/issues/35161

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/TceMain.php';
*/

?>