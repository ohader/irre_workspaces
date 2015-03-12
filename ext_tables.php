<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/*
t3lib_div::loadTCA('tt_content');
// Enable field pi_flexform to be displayed in workspace record details view:
if (!t3lib_div::inList($GLOBALS['TCA']['tt_content']['interface']['showRecordFieldList'], 'pi_flexform')) {
	$GLOBALS['TCA']['tt_content']['interface']['showRecordFieldList'] .= ',pi_flexform';
}
*/

\TYPO3\CMS\Workspaces\Service\AdditionalColumnService::getInstance()->register(
	'IrreWorkspaces_LastEditor',
	'OliverHader\\IrreWorkspaces\\Service\\Grid\\ColumnDataProvider'
);

\TYPO3\CMS\Workspaces\Service\AdditionalResourceService::getInstance()->addJavaScriptResource(
	'IrreWorkspaces.Grid.LastEditor',
	'EXT:irre_workspaces/Resources/Public/JavaScript/Grid/LastEditor.js'
);
\TYPO3\CMS\Workspaces\Service\AdditionalResourceService::getInstance()->addLocalizationResource(
	'EXT:irre_workspaces/Resources/Private/Language/locallang.xlf'
);
?>