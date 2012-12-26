Ext.ns('TYPO3.TxIrreWorkspaces');

TYPO3.TxIrreWorkspaces.PagesStore = new Ext.data.DirectStore({
	storeId : 'TxIrreWorkspacesPagesStore',
	root : 'data',
	idProperty : 'uid',
	fields : [
		{name: 'uid'},
		{name: 'pid'}
	]
});

Ext.onReady(function() {
	TYPO3.TxIrreWorkspaces.PagesStore.proxy = new Ext.data.DirectProxy({
		directFn : TYPO3.Workspaces.ExtDirectTxIrreWorkspacesActions.getPages
	});
	TYPO3.TxIrreWorkspaces.PagesStore.load();
});