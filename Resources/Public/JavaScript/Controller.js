Ext.namespace('TYPO3.TxIrreWorkspaces');

TYPO3.TxIrreWorkspaces.Controller = {
	isHandlerActive: false,

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

	handleGridRowSelectEvent: function(selection, index, row) {
		if (!TYPO3.TxIrreWorkspaces.Controller.isHandlerActive) {
			TYPO3.TxIrreWorkspaces.Controller.isHandlerActive = true;
			TYPO3.TxIrreWorkspaces.Controller.findCollectionElements(
					selection,
					index,
					row,
					true
			);
			TYPO3.TxIrreWorkspaces.Controller.isHandlerActive = false;
		}
	},

	handleGridRowDeselectEvent: function(selection, index, row) {
		if (!TYPO3.TxIrreWorkspaces.Controller.isHandlerActive) {
			TYPO3.TxIrreWorkspaces.Controller.isHandlerActive = true;
			TYPO3.TxIrreWorkspaces.Controller.findCollectionElements(
					selection,
					index,
					row,
					false
			);
			TYPO3.TxIrreWorkspaces.Controller.isHandlerActive = false;
		}
	},

	findCollectionElements: function(selection, currentIndex, currentRow, isSelect) {
		var currentValue = currentRow.json.Tx_IrreWorkspaces_Collection;

		if (currentValue) {
			selection.grid.getStore().each(function(row, index) {
				var value = row.json.Tx_IrreWorkspaces_Collection;

				if (value === currentValue && index !== currentIndex) {
					if (isSelect) {
						selection.selectRow(index, true);
					} else {
						selection.deselectRow(index);
					}
				}
			});
		}
	}
};
