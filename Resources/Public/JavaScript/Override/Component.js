TYPO3.Workspaces.MainStore = new Ext.ux.MultiGroupingStore({
	storeId : 'workspacesMainStore',
	reader : new Ext.data.JsonReader({
		idProperty : 'uid',
		root : 'data',
		totalProperty : 'total'
	}, TYPO3.Workspaces.Configuration.StoreFieldArray),
	groupField: ['path_Workspace', 'Tx_IrreWorkspaces_Collection'],
	paramsAsHash : true,
	sortInfo : {
		field : 'label_Live',
		direction : "ASC"
	},
	remoteSort : true,
	baseParams: {
		depth : 990,
		id: TYPO3.settings.Workspaces.id,
		query: '',
		start: 0,
		limit: 10
	},

	showAction : false,
	listeners : {
		beforeload : function() {
		},
		load : function(store, records) {
		},
		datachanged : function(store) {
		},
		scope : this
	}
});
