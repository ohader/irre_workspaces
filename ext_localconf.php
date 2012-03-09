<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Modify the bahaviour of the workspace module
// Basically the XLCASSes are required to inject behaviour and JavaScript code

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/GridData.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_Service_GridData.php';

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/ExtDirect/ActionHandler.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_ExtDirect_ActionHandler.php';

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Controller/ReviewController.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_Controller_ReviewController.php';

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_pagerenderer.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/PageRenderer.php';

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/TceMain.php';

?>