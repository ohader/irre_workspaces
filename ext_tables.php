<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

	// Avoids blocking the frontend and install tool
if (TYPO3_MODE == 'BE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
	t3lib_extMgm::registerExtDirectComponent(
		'TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions',
		t3lib_extMgm::extPath($_EXTKEY) . 'Classes/ExtDirect/ActionHandler.php:Tx_IrreWorkspaces_ExtDirect_ActionHandler',
		'web_WorkspacesWorkspaces',
		'user,group'
	);
}

?>