<?php
defined('TYPO3_MODE') or die('Access denied.');

\OliverHader\IrreWorkspaces\Bootstrap::registerHooks();
\OliverHader\IrreWorkspaces\Bootstrap::registerSlots();
\OliverHader\IrreWorkspaces\Bootstrap::registerAlternatives();

// Basically the XLCASSes are required to inject behaviour and JavaScript code

// @todo: Might use signal-dispatcher for TYPO3 4.7, see http://forge.typo3.org/issues/35166

/*
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/GridData.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_Service_GridData.php';
*/

// @todo: Might use signal-dispatcher for TYPO3 4.7, see http://forge.typo3.org/issues/35175

/*
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/ExtDirect/Server.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_ExtDirect_Server.php';
*/

// XClasses for handling workspaces actions
/*
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/version/class.tx_version_tcemain.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Version_TceMain.php';
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/version/class.tx_version_tcemain_commandmap.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Version_TceMain_CommandMap.php';
*/

/*
DISABLED - Since the workspace module does not show unmodified elements,
there is no need to manipulate data structures (without changes) on editing

// @todo: Might use processDatamap_beforeStart for TYPO3 4.7, see http://forge.typo3.org/issues/35161

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/T3lib_TceMain.php';
*/

?>