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

TYPO3.Workspaces.Actions.__original__checkIntegrity = TYPO3.Workspaces.Actions.checkIntegrity;
TYPO3.Workspaces.Actions.checkIntegrity = function(parameters, callbackFunction, callbackArguments) {
	if (parameters.type === 'all') {
		parameters.stage = TYPO3.Workspaces.Toolbar.StageSelector.getValue();
	}

	TYPO3.Workspaces.Actions.__original__checkIntegrity(parameters, callbackFunction, callbackArguments);
};

TYPO3.Workspaces.Actions.__original__runMassAction = TYPO3.Workspaces.Actions.runMassAction;
TYPO3.Workspaces.Actions.runMassAction = function(parameters) {
	parameters.stage = TYPO3.Workspaces.Toolbar.StageSelector.getValue();
	TYPO3.Workspaces.Actions.__original__runMassAction(parameters);
};