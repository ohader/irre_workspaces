Ext.namespace('TYPO3.TxIrreWorkspaces');

TYPO3.TxIrreWorkspaces.Controller = {
	isHandlerActive: false,
	headerClassName: 'x-grid3-hd-checker-on',

	handleSelectionStoreLoadEvent: function(store, records) {
		if (records.length == 0) {
			TYPO3.Workspaces.Toolbar.selectionActionCombo.hide();
		} else {
			TYPO3.Workspaces.Toolbar.selectionActionCombo.show();
		}
	},

	handleSelectionModelSelectionChangeEvent: function (selection) {
		if (selection.grid.getSelectionModel().getSelections().length > 0) {
			TYPO3.Workspaces.Toolbar.selectionActionCombo.setDisabled(false);
		} else {
			TYPO3.Workspaces.Toolbar.selectionActionCombo.setDisabled(true);
		}
	},

	pageTreeSelect: function(pageId) {
		if (Ext.isDefined(pageId) && top && top.TYPO3.TxIrreWorkspaces.PageTree) {
			top.TYPO3.TxIrreWorkspaces.PageTree.select(pageId);
		}
	}
};
