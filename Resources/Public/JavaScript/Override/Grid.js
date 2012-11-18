/**
 * Handles selection of all related IRRE elements
 * if one single of that collection has been selected
 *
 * @disabled in favour of handling (de-)select all using a header checkbox
 * @see TYPO3.TxIrreWorkspaces.Controller.handleGridViewGroupSelectEvent
 */
/*
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
*/

TYPO3.Workspaces.SelectionModel.addListener(
	'selectionchange',
	TYPO3.TxIrreWorkspaces.Controller.handleSelectionModelSelectionChangeEvent,
	TYPO3.Workspaces.SelectionModel
);

TYPO3.Workspaces.WorkspaceGrid = new Ext.ux.MultiGroupingGrid({
	initColModel: function() {
		if (TYPO3.settings.Workspaces.isLiveWorkspace) {
			this.colModel = new Ext.grid.ColumnModel({
				columns: [
					TYPO3.Workspaces.RowExpander,
					TYPO3.Workspaces.Configuration.Integrity,
					{id: 'uid', dataIndex : 'uid', width: 40, sortable: true, header : TYPO3.lang["column.uid"], hidden: true, filterable : true },
					{id: 't3ver_oid', dataIndex : 't3ver_oid', width: 40, sortable: true, header : TYPO3.lang["column.oid"], hidden: true, filterable : true },
					{id: 'workspace_Title', dataIndex : 'workspace_Title', width: 120, sortable: true, header : TYPO3.lang["column.workspaceName"], hidden: true, filter : {type : 'string'}},
					TYPO3.Workspaces.Configuration.TxIrreWorkspacesCollection,
					TYPO3.Workspaces.Configuration.WsPath,
					TYPO3.Workspaces.Configuration.Language,
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
					TYPO3.Workspaces.Configuration.Integrity,
					{id: 'uid', dataIndex : 'uid', width: 40, sortable: true, header : TYPO3.lang["column.uid"], hidden: true, filterable : true },
					{id: 't3ver_oid', dataIndex : 't3ver_oid', width: 40, sortable: true, header : TYPO3.lang["column.oid"], hidden: true, filterable : true },
					{id: 'workspace_Title', dataIndex : 'workspace_Title', width: 120, sortable: true, header : TYPO3.lang["column.workspaceName"], hidden: true, filter : {type : 'string'}},
					TYPO3.Workspaces.Configuration.TxIrreWorkspacesCollection,
					TYPO3.Workspaces.Configuration.WsPath,
					TYPO3.Workspaces.Configuration.Language,
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
	trackMouseOver: true,

	plugins : [
		TYPO3.Workspaces.RowExpander,
		TYPO3.Workspaces.Configuration.GridFilters,
		new Ext.ux.plugins.FitToParent()
	],

	listeners: {
		groupmousedown: TYPO3.TxIrreWorkspaces.Controller.handleGridViewGroupExpandEvent,
		beforegroupmousedown: TYPO3.TxIrreWorkspaces.Controller.handleGridViewGroupSelectEvent
	},

	view : TYPO3.TxIrreWorkspaces.Helper.createMultiGroupingView(),

	bbar : TYPO3.Workspaces.Toolbar.FullBottomBar,
	tbar : TYPO3.Workspaces.Toolbar.FullTopToolbar
});