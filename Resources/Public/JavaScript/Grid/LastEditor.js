Ext.ns('TYPO3.Workspaces.extension.AdditionalColumn');

TYPO3.Workspaces.extension.AdditionalColumn.IrreWorkspaces_LastEditor = {
	id: 'IrreWorkspaces_LastEditor',
	dataIndex : 'IrreWorkspaces_LastEditor',
	width: 120,
	sortable: true,
	header: TYPO3.l10n.localize('AdditionalColumn.IrreWorkspaces_LastEditor.header'),
	renderer: function(value, metaData, record, rowIndex, colIndex, store) {
		var date;
		if (record.json.IrreWorkspaces_LastEditor) {
			return record.json.IrreWorkspaces_LastEditor;
		}
	},
	hidden: false
};