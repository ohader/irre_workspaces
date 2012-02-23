Ext.namespace('TYPO3.TxIrreWorkspaces');

TYPO3.TxIrreWorkspaces.Actions = {
	executeSelectionAction: function(data) {
		var method, records = [], parameters;

		Ext.each(data.selection, function(row) {
			records.push({
				table: row.json.table,
				t3ver_oid: row.json.t3ver_oid,
				uid: row.json.uid
			});
		});

		switch (data.action) {
			case 'publish':
				method = TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions.publishWorkspace;
				break;
			case 'swap':
				method = TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions.swpaWorkspace;
				break;
			case 'discard':
				method = TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions.flushWorkspace;
				break;
		}

		parameters = {
			method: method,
			records: records
		};

		method(
			parameters,
			TYPO3.TxIrreWorkspaces.Actions.executeSelectionActionCallback
		);

		top.Ext.getCmp('executeMassActionForm').update('Working...');
		top.Ext.getCmp('executeMassActionOkButton').disable();
	},

	executeSelectionActionCallback: function(response) {
		if (response.error) {
			top.Ext.getCmp('executeMassActionOkButton').hide();
			top.Ext.getCmp('executeMassActionCancleButton').setText(TYPO3.lang.close);
			top.Ext.getCmp('executeMassActionForm').update('<strong>Error:</strong><br/>' + response.error);
		} else {
			top.Ext.getCmp('executeMassActionOkButton').hide();
			top.Ext.getCmp('executeMassActionCancleButton').setText(TYPO3.lang.close);
			top.Ext.getCmp('executeMassActionForm').update('Done. Processed %s record(s)'.replace('%s', response.total));
			top.TYPO3.Backend.NavigationContainer.PageTree.refreshTree();
		}
	}
};

