PageTSconfig properties
=======================

* Possibility to define preview page-id for regular records

	options.workspaces.previewPageId = <pageId> | field:<fieldName>
	options.workspaces.previewPageId.<tableName> = <pageId> | field:<fieldName>

	Examples:
	* options.workspaces.previewPageId.tx_myext_table = 123
	* options.workspaces.previewPageId.tx_myext_table = field:pid


General
=======

* Expanding pages with dynamic nested TreeLoader (ExtJS)

	TYPO3.TxIrreWorkspaces.PageTree.select(<pageId>);
