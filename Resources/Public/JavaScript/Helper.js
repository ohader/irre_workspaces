Ext.namespace('TYPO3.TxIrreWorkspaces');

TYPO3.TxIrreWorkspaces.Helper = {
	createMultiGroupingView: function() {
		var groupTextTpl = '{text}: {[values.group_level == 0 ? values.gvalue : values.rs[0].data.Tx_IrreWorkspaces_Title]} ({[values.rs.length]} {[values.rs.length > 1 ? "' + TYPO3.lang["items"] + '" : "' + TYPO3.lang["item"] + '"]})';
		var startGroup = new Ext.XTemplate(
			'<div id="{groupId}" class="x-grid-group {cls}">',
				'<div id="{groupId}-hd" class="x-grid-group-hd {[values.group_level > 0 ? "x-grid-TxIrreWorkspaces-checkable" : ""]}" style="{style}" data-collection-id="{[values.rs[0].data.Tx_IrreWorkspaces_Collection]}">' ,
					'<div class="x-grid3-hd-checker">&#160;</div>',
					'<div class="x-grid-group-title">', groupTextTpl , '</div>',
					'<div class="x-grid-clear"><!-- // --></div>',
				'</div>',
				'<div id="{groupId}-bd" class="x-grid-group-body">'
		);

		return new Ext.ux.MultiGroupingView({
			startGroup: startGroup,
			groupTextTpl : groupTextTpl,

			forceFit: true,
			enableGroupingMenu: false,
			displayEmptyFields: true,
			removeEmptyFieldsGroups: true,
			enableNoGroups: false,
			hideGroupedColumn: true
		})
	}
};
