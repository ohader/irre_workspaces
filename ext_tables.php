<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

	// Avoids blocking the frontend and install tool
if (TYPO3_MODE == 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
/*
	t3lib_extMgm::registerExtDirectComponent(
		'TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions',
		t3lib_extMgm::extPath($_EXTKEY) . 'Classes/ExtDirect/ActionHandler.php:Tx_IrreWorkspaces_ExtDirect_ActionHandler',
		'web_WorkspacesWorkspaces',
		'user,group'
	);
*/
}

/*
t3lib_div::loadTCA('tt_content');
// Enable field pi_flexform to be displayed in workspace record details view:
if (!t3lib_div::inList($GLOBALS['TCA']['tt_content']['interface']['showRecordFieldList'], 'pi_flexform')) {
	$GLOBALS['TCA']['tt_content']['interface']['showRecordFieldList'] .= ',pi_flexform';
}
*/

/*
require_once t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/SysWorkspace.php';
require_once t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/SysWorkspaceStage.php';
*/
?>