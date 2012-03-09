TYPO3.Workspaces.Actions.sendToSpecificStageWindow = function(selection, nextStage) {
	var elements = [];

	Ext.each(selection, function(row) {
		elements.push({table: row.json.table, uid: row.json.uid})
	});

	TYPO3.Workspaces.ExtDirectActions.sendToSpecificStageWindow(nextStage, elements, function(response) {
		TYPO3.Workspaces.Actions.currentSendToMode = 'specific';
		TYPO3.Workspaces.Actions.sendToStageWindow(response, selection);
	});
};
