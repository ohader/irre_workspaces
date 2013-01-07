Ext.ns('TYPO3.TxIrreWorkspaces');

TYPO3.TxIrreWorkspaces.PageTree = {
/**
	listenNavigationContainerChange: function(navigationContainer, component) {
		if (component && component.getId() === 'typo3-pagetree') {
			component.addListener('show', TYPO3.TxIrreWorkspaces.PageTree.listPageTreeShow);
		}
	},

	listPageTreeShow: function(component) {
		var timer;
		var f = function() {
			if(TYPO3.Backend.NavigationContainer.PageTree.getTree()) {
				clearInterval(timer);
				TYPO3.TxIrreWorkspaces.PageTree.modifyPageTree();
			}
		};
		timer = setInterval(f, 200);
	},

	modifyPageTree: function() {
		Ext.each(
			TYPO3.Backend.NavigationContainer.PageTree.getTree().getLoader().events.beforeload.listeners,
			TYPO3.TxIrreWorkspaces.PageTree.purgeBeforeLoadListener
		);

		TYPO3.Backend.NavigationContainer.PageTree.getTree().getLoader().addListener(
			'beforeload',
			TYPO3.TxIrreWorkspaces.PageTree.beforeLoadHandler
		);
	},

	purgeBeforeLoadListener: function(listener) {
		if (listener.fn.toString().match(/treeLoader\.baseParams/)) {
			TYPO3.Backend.NavigationContainer.PageTree.getTree().getLoader().removeListener(
				'beforeload',
				listener.fn
			);
		}
	},

	beforeLoadHandler: function(treeLoader, node) {
		var attributes = false;

		if (node.attributes && node.attributes.nodeData) {
			attributes = node.attributes.nodeData;
		}

		treeLoader.baseParams.nodeId = node.id;
		treeLoader.baseParams.attributes = attributes;
	},
*/

	/**
	 * Selects a node defined by the page id. If the second parameter is set, we
	 * store the new location into the state hash.
	 *
	 * @param {int} pageId
	 * @param {Boolean} saveState
	 * @return {Boolean}
	 */
	select: function(pageId, saveState) {
		if (TYPO3.TxIrreWorkspaces.PageTree.getNode(pageId)) {
			TYPO3.Backend.NavigationContainer.PageTree.select(pageId, saveState);
		} else {
			TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions.getRootLine(pageId, function(response) {
				if (Ext.isArray(response)) {
					TYPO3.TxIrreWorkspaces.PageTree.expandRootLine(response, pageId, saveState);
				}
			});
		}
	},

	expandRootLine: function(rootLine, pageId, saveState) {
		var pageId;
		var currentNode, previousNode;
		var successful = true;

		while (rootLine.length) {
			pageId = rootLine.first();
			currentNode = TYPO3.TxIrreWorkspaces.PageTree.getNode(pageId);

			if (!currentNode && previousNode) {
				successful = false;
				previousNode.expand(false, false, function() {
					TYPO3.TxIrreWorkspaces.PageTree.expandRootLine(rootLine, pageId, saveState)
				});
				break;
			} else {
				previousNode = currentNode;
				rootLine.shift();
			}
		}

		if (successful) {
			TYPO3.Backend.NavigationContainer.PageTree.select(pageId, saveState);
		}
	},

	getNode: function(pageId) {
		return TYPO3.Backend.NavigationContainer.PageTree.getTree().getRootNode().findChild('realId', pageId, true);
	}
};

/**
Ext.onReady(function() {
	Only required to override Core TreeLoader
	TYPO3.Backend.NavigationContainer.addListener('add', TYPO3.TxIrreWorkspaces.PageTree.listenNavigationContainerChange);
});
*/