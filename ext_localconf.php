<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Modify the bahaviour of the workspace module

// Hook to add additional stylesheet and JavaScript resources to Workspaces Module:
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ReviewControllerResourcesHook.php:Tx_IrreWorkspaces_Hooks_ReviewControllerResourcesHook->renderPreProcess';

// Modify the bahaviour of the workspace module

// Hook to e.g. modify the differences view and exclude l10n_diffsource fields:
$TYPO3_CONF_VARS['SC_OPTIONS']['workspaces']['modifyDifferenceArray'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ExtDirectServerHook.php:Tx_IrreWorkspaces_Hooks_ExtDirectServerHook';


// Hook to pre- and post-process values (required for FlexForm rendering)
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook->preProcess';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook->postProcess';
$TYPO3_CONF_VARS['SC_OPTIONS']['workspaces']['modifyDifferenceArray'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook';

// Hook to visualize current target page if editing a workspace element
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/alt_doc.php']['makeEditForm_accessCheck'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/PageTreeVisualizationHook.php:Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook->handleEditing';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['updateSignalHook']['tx_irreworkspaces::updateEditing'] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/PageTreeVisualizationHook.php:Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook->updateEditing';



// Basically the XLCASSes are required to inject behaviour and JavaScript code

// @todo: Might use signal-dispatcher for TYPO3 4.7, see http://forge.typo3.org/issues/35166

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/GridData.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_Service_GridData.php';

// @todo: Might use signal-dispatcher for TYPO3 4.7, see http://forge.typo3.org/issues/35175

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/ExtDirect/ActionHandler.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_ExtDirect_ActionHandler.php';

/*
DISABLED - Since the workspace module does not show unmodified elements,
there is no need to manipulate data structures (without changes) on editing

// @todo: Might use processDatamap_beforeStart for TYPO3 4.7, see http://forge.typo3.org/issues/35161

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/TceMain.php';
*/

?>