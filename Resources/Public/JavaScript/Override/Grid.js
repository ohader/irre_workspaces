/**
 * Handles selection of all related IRRE elements
 * if one single of that collection has been selected
 *
 * @disabled in favour of handling (de-)select all using a header checkbox
 * @see TYPO3.TxIrreWorkspaces.Controller.handleGridViewGroupSelectEvent
 */

if (!TYPO3.settings.TxIrreWorkspaces.enableRecordSinglePublish) {
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
}

TYPO3.Workspaces.SelectionModel.addListener(
	'selectionchange',
	TYPO3.TxIrreWorkspaces.Controller.handleSelectionModelSelectionChangeEvent,
	TYPO3.Workspaces.SelectionModel
);
