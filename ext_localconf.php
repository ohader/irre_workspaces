<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/GridData.php'] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Classes/XClasses/Tx_Workspaces_Service_GridData.php';

?>