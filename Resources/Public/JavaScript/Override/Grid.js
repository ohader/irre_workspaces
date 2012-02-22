TYPO3.Workspaces.SelectionModel.addListener(
	'rowselect',
	TYPO3.TxIrreWorkspaces.Controller.handleGridRowSelectEvent,
	TYPO3.Workspaces.SelectionModel
);

TYPO3.Workspaces.SelectionModel.addListener(
	'rowdeselect',
	TYPO3.TxIrreWorkspaces.Controller.handleGridRowDeselectEvent,
	TYPO3.Workspaces.SelectionModel
);

TYPO3.Workspaces.WorkspaceGrid = new Ext.ux.MultiGroupingGrid({
	initColModel: function() {
		if (TYPO3.settings.Workspaces.isLiveWorkspace) {
			this.colModel = new Ext.grid.ColumnModel({
				columns: [
					TYPO3.Workspaces.RowExpander,
					{id: 'uid', dataIndex : 'uid', width: 40, sortable: true, header : TYPO3.lang["column.uid"], hidden: true, filterable : true },
					{id: 't3ver_oid', dataIndex : 't3ver_oid', width: 40, sortable: true, header : TYPO3.lang["column.oid"], hidden: true, filterable : true },
					{id: 'workspace_Title', dataIndex : 'workspace_Title', width: 120, sortable: true, header : TYPO3.lang["column.workspaceName"], hidden: true, filter : {type : 'string'}},
					TYPO3.Workspaces.Configuration.TxIrreWorkspacesCollection,
					TYPO3.Workspaces.Configuration.WsPath,
					TYPO3.Workspaces.Configuration.LivePath,
					TYPO3.Workspaces.Configuration.WsTitleWithIcon,
					TYPO3.Workspaces.Configuration.TitleWithIcon,
					TYPO3.Workspaces.Configuration.ChangeDate
				],
				listeners: {

					columnmoved: function(colModel) {
						TYPO3.Workspaces.Actions.updateColModel(colModel);
					},
					hiddenchange: function(colModel) {
						TYPO3.Workspaces.Actions.updateColModel(colModel);
					}
				}
			});
		} else {
				this.colModel = new Ext.grid.ColumnModel({
				columns: [
					TYPO3.Workspaces.SelectionModel,
					TYPO3.Workspaces.RowExpander,
					{id: 'uid', dataIndex : 'uid', width: 40, sortable: true, header : TYPO3.lang["column.uid"], hidden: true, filterable : true },
					{id: 't3ver_oid', dataIndex : 't3ver_oid', width: 40, sortable: true, header : TYPO3.lang["column.oid"], hidden: true, filterable : true },
					{id: 'workspace_Title', dataIndex : 'workspace_Title', width: 120, sortable: true, header : TYPO3.lang["column.workspaceName"], hidden: true, filter : {type : 'string'}},
					TYPO3.Workspaces.Configuration.TxIrreWorkspacesCollection,
					TYPO3.Workspaces.Configuration.WsPath,
					TYPO3.Workspaces.Configuration.LivePath,
					TYPO3.Workspaces.Configuration.WsTitleWithIcon,
					TYPO3.Workspaces.Configuration.SwapButton,
					TYPO3.Workspaces.Configuration.TitleWithIcon,
					TYPO3.Workspaces.Configuration.ChangeDate,
					TYPO3.Workspaces.Configuration.ChangeState,
					TYPO3.Workspaces.Configuration.Stage,
					TYPO3.Workspaces.Configuration.RowButtons
				],
				listeners: {

					columnmoved: function(colModel) {
						TYPO3.Workspaces.Actions.updateColModel(colModel);
					},
					hiddenchange: function(colModel) {
						TYPO3.Workspaces.Actions.updateColModel(colModel);
					}
				}
			});
		}

	},
	border : true,
	store : TYPO3.Workspaces.MainStore,
	colModel : null,

	sm: TYPO3.Workspaces.SelectionModel,
	loadMask : true,
	height: 630,
	stripeRows: true,
		// below the grid we need 40px space for the legend
	heightOffset: 40,
	plugins : [
		TYPO3.Workspaces.RowExpander,
		TYPO3.Workspaces.Configuration.GridFilters,
		new Ext.ux.plugins.FitToParent()
	],
	view : new Ext.ux.MultiGroupingView({
		forceFit: true,
		groupTextTpl : '{text}: {[values.group_level == 0 ? values.gvalue : values.rs[0].json.label_Workspace]} ({[values.rs.length]} {[values.rs.length > 1 ? "' + TYPO3.lang["items"] + '" : "' + TYPO3.lang["item"] + '"]})',
		enableGroupingMenu: false,
		displayEmptyFields: true,
		removeEmptyFieldsGroups: true,
  		enableNoGroups: false,
		hideGroupedColumn: true
	}),
	bbar : TYPO3.Workspaces.Toolbar.FullBottomBar,
	tbar : TYPO3.Workspaces.Toolbar.FullTopToolbar
});