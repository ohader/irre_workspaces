<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array(
	'tx_irreworkspaces_recipient_mode' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:irre_workspaces/locallang_db.xml:sys_workspace_stage.tx_irreworkspaces_recipient_mode',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:irre_workspaces/locallang_db.xml:sys_workspace.tx_irreworkspaces.recipient_mode.I.0', '0'),
				array('LLL:EXT:irre_workspaces/locallang_db.xml:sys_workspace.tx_irreworkspaces.recipient_mode.I.1', '1'),
				array('LLL:EXT:irre_workspaces/locallang_db.xml:sys_workspace.tx_irreworkspaces.recipient_mode.I.2', '2'),
				array('LLL:EXT:irre_workspaces/locallang_db.xml:sys_workspace.tx_irreworkspaces.recipient_mode.I.9', '9'),
			),
			'size' => 1,
			'maxitems' => 1,
			'default' => '0',
		)
	),
);


t3lib_div::loadTCA('sys_workspace_stage');
t3lib_extMgm::addTCAcolumns('sys_workspace_stage',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes(
	'sys_workspace_stage',
	'tx_irreworkspaces_recipient_mode',
	'',
	'after:title'
);
?>