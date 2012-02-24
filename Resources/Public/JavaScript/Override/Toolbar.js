TYPO3.Workspaces.Toolbar.selectMassActionStore.addListener(
	'load', TYPO3.TxIrreWorkspaces.Controller.handleSelectionStoreLoadEvent
);

TYPO3.Workspaces.Toolbar.selectionActionCombo = new Ext.form.ComboBox({
	width: 150,
	lazyRender: true,
	valueField: 'action',
	displayField: 'title',
	mode: 'local',
	emptyText: 'choose selection action',
	selectOnFocus: true,
	triggerAction: 'all',
	editable: false,
	disabled : true,
	hidden : true,	 // we hide it by default and show it in case there are any actions available
	forceSelection: true,
	store: TYPO3.Workspaces.Toolbar.selectMassActionStore,
	listeners: {
		'select' : function (combo, record) {
			var label = '';
			var affectWholeWorkspaceWarning = TYPO3.lang["tooltip.affectWholeWorkspace"];
			var selection = TYPO3.Workspaces.WorkspaceGrid.getSelectionModel().getSelections();

			switch (record.data.action) {
				case 'publish':
					label = 'Ready to public selected records';// TYPO3.lang["tooltip.publishAll"];
					break;
				case 'swap':
					label = 'Ready to swap selected records'; // TYPO3.lang["tooltip.swapAll"];
					break;
				case 'discard':
					label = 'Ready to discard selected records'; // TYPO3.lang["tooltip.discardAll"];
					break;
			}

			top.TYPO3.Windows.close('executeMassActionWindow');

			var dialog = top.TYPO3.Windows.showWindow({
				id: 'executeMassActionWindow',
				title: TYPO3.lang["window.massAction.title"],
				items: [
					{
						xtype: 'form',
						id: 'executeMassActionForm',
						width: '100%',
						html: label,
						bodyStyle: 'padding: 5px 5px 3px 5px; border-width: 0; margin-bottom: 7px;'
					}
				],
				buttons: [
					{
						id: 'executeMassActionOkButton',
						data: {action: record.data.action, selection: selection},
						scope: this,
						text: TYPO3.lang.ok,
						disabled:false,
						handler: function(event) {
							TYPO3.TxIrreWorkspaces.Actions.executeSelectionAction(event.data);
						}
					},
					{
						id: 'executeMassActionCancleButton',
						scope: this,
						text: TYPO3.lang.cancel,
						handler: function() {
							top.TYPO3.Windows.close('executeMassActionWindow');
							// if clicks during action - this also interrupts the running process -- not the nices way but efficient
							top.TYPO3.ModuleMenu.App.reloadFrames();
						}
					}
				]
			});
		}
	}
});

TYPO3.Workspaces.Toolbar.FullBottomBar = [
	(TYPO3.settings.Workspaces.isLiveWorkspace == true) ? {hidden: true} : TYPO3.Workspaces.Toolbar.selectionActionCombo,
	(TYPO3.settings.Workspaces.isLiveWorkspace == true) ? {hidden: true} : '-',
	(TYPO3.settings.Workspaces.isLiveWorkspace == true) ? {hidden: true} : TYPO3.Workspaces.Toolbar.selectStateActionCombo,
	(TYPO3.settings.Workspaces.isLiveWorkspace == true) ? {hidden: true} : '-',
	(TYPO3.settings.Workspaces.isLiveWorkspace == true) ? {hidden: true} : TYPO3.Workspaces.Toolbar.selectStateMassActionCombo,

	{xtype: 'tbfill'},
	TYPO3.Workspaces.Toolbar.Pager
];