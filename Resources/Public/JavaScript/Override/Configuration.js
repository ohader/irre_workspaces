TYPO3.Workspaces.Configuration.StoreFieldArray.push(
	{ name : 'Tx_IrreWorkspaces_Collection' }
);

TYPO3.Workspaces.Configuration.TxIrreWorkspacesCollection = {
	id: 'Tx_IrreWorkspaces_Collection',
	dataIndex : 'Tx_IrreWorkspaces_Collection',
	header: 'Element',
	width: 120,
	hidden: true,
	hideable: false,
	sortable: true,
	renderer: function(value, metaData, record, rowIndex, colIndex, store) {
		var value = record.json.Tx_IrreWorkspaces_Collection;
		return value;
	},
	filter : {type: 'string'}
};
