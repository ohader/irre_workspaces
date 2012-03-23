<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Modify the bahaviour of the workspace module
// Hook to add additional stylesheet and JavaScript resources to Workspaces Module:

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
	'EXT:' . $_EXTKEY . '/Classes/Hooks/ReviewControllerResourcesHook.php:Tx_IrreWorkspaces_Hooks_ReviewControllerResourcesHook->renderPreProcess';

// Modify the bahaviour of the workspace module
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