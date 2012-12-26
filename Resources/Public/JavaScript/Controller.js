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
			TYPO3.TxIrreWorkspaces.Controller.setCollectionElementsByCurrentRow(
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
			TYPO3.TxIrreWorkspaces.Controller.setCollectionElementsByCurrentRow(
					selection,
					index,
					row,
					false
			);
			TYPO3.TxIrreWorkspaces.Controller.isHandlerActive = false;
		}
	},

	setCollectionElementsByCurrentRow: function(selection, currentIndex, currentRow, isSelect) {
		var currentValue = currentRow.json.Tx_IrreWorkspaces_Collection;
		TYPO3.TxIrreWorkspaces.Controller.setCollectionElementsByCurrentValue(
			selection,
			currentIndex,
			currentValue,
			isSelect
		);
	},

	setCollectionElementsByCurrentValue: function(selection, currentIndex, currentValue, isSelect) {
		currentValue = parseInt(currentValue);

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
	},

	/**
	 * Handles additional actions on expanding/collapsing a group element.
	 *
	 * @param grid
	 * @param field
	 * @param groupValue
	 * @param e
	 */
	handleGridViewGroupExpandEvent: function(grid, field, groupValue, e) {
		var group, pageId, expanded
		var hd = e.getTarget('.x-grid-group-hd', grid.getView().mainBody);

		if (hd) {
			// "expanded" has the state after(!) the event,
			// thus if it was just expanded then the value is true
			expanded = Ext.get(hd.parentNode).hasClass('x-grid-group-collapsed');
			group = grid.getView().getGroupById(hd.parentNode.id);

			// Group level "0" are pages
			if (group && group.group_level === 0) {
				pageId = group.rs[0].data.livepid;
				TYPO3.TxIrreWorkspaces.Controller.pageTreeSelect(
					expanded ? pageId : TYPO3.settings.Workspaces.id
				);
			}
		}
	},

	pageTreeSelect: function(pageId) {
		var node, result = false;

		if (Ext.isDefined(pageId) && top && top.TYPO3.Backend.NavigationContainer.PageTree) {
			node = TYPO3.TxIrreWorkspaces.Controller.expandParentsInPageTree(pageId);
			result = top.TYPO3.Backend.NavigationContainer.PageTree.select(pageId);
		}

		return result;
	},

	expandParentsInPageTree: function(pageId) {
		var tree = top.TYPO3.Backend.NavigationContainer.PageTree.getTree();
		var node = tree.getRootNode().findChild('realId', pageId, true);
		var parentNode;

		if (!node) {
			parentNode = TYPO3.TxIrreWorkspaces.PagesStore.getById(pageId);

			if (parentNode && parentNode.data.pid) {
				TYPO3.TxIrreWorkspaces.Controller.expandParentsInPageTree(parentNode.data.pid);
				node = tree.getRootNode().findChild('realId', pageId, true);
			}
		}

		if(node && !node.leaf) {
			node.expand(false, false);
		}

		return node;
	},

	/**
	 * Handles additional actions on selecting a group element
	 * (selects all sub elements as well)
	 *
	 * @param grid
	 * @param field
	 * @param groupValue
	 * @param e
	 * @return {Boolean} Whether to stop handling this event
	 * @see Ext.ux.MultiGroupingView.processEvent
	 */
	handleGridViewGroupSelectEvent: function(grid, field, groupValue, e) {
		var parent, selected, collectionId,
			mainBody = grid.getView().mainBody,
			checker = e.getTarget('.x-grid3-hd-checker', mainBody, true);

		if (checker) {
			// Toggles view and assigns data
			parent = checker.findParent('.x-grid-group-hd', mainBody, true);
			parent.toggleClass('x-grid3-hd-checker-on');
			selected = parent.hasClass('x-grid3-hd-checker-on');
			collectionId = parent.getAttribute('data-collection-id');

			// Selects all related IRRE elements of this collection
			TYPO3.TxIrreWorkspaces.Controller.setCollectionElementsByCurrentValue(
				grid.getSelectionModel(),
				null,
				collectionId,
				selected
			);

			// Stops processing of this event
			return false;
		}
	}
};
