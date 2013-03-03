Ext.onReady(function() {
	TYPO3.Workspaces.Toolbar.StageSelector.getStore().proxy = new Ext.data.DirectProxy({
		directFn : TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions.getAllowedStages
	});
	TYPO3.Workspaces.Toolbar.StageSelector.getStore().load();
});