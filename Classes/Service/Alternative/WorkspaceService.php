<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Oliver Hader <oliver.hader@typo3.org>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class Tx_IrreWorkspaces_Service_Alternative_WorkspaceService extends Tx_Workspaces_Service_Workspaces implements t3lib_Singleton {
	/**
	 * @var integer
	 */
	protected $stage;

	/**
	 * @return Tx_IrreWorkspaces_Service_Alternative_WorkspaceService
	 */
	static public function getInstance() {
		return t3lib_div::makeInstance(
			'Tx_IrreWorkspaces_Service_Alternative_WorkspaceService'
		);
	}

	public function __construct() {
		$this->reset();
	}

	public function reset() {
		$this->setStage();
	}

	/**
	 * @param integer $stage
	 */
	public function setStage($stage = -99) {
		$this->stage = (int) $stage;
	}

	/**
	 * Select all records from workspace pending for publishing
	 * Used from backend to display workspace overview
	 * User for auto-publishing for selecting versions for publication
	 *
	 * @param	integer $wsid Workspace ID. If -99, will select ALL versions from ANY workspace. If -98 will select all but ONLINE. >=-1 will select from the actual workspace
	 * @param	integer $filter Lifecycle filter: 1 = select all drafts (never-published), 2 = select all published one or more times (archive/multiple), anything else selects all.
	 * @param	integer $stage Stage filter: -99 means no filtering, otherwise it will be used to select only elements with that stage. For publishing, that would be "10"
	 * @param	integer $pageId Page id: Live page for which to find versions in workspace!
	 * @param	integer $recursionLevel Recursion Level - select versions recursive - parameter is only relevant if $pageId != -1
	 * @param	string $selectionType How to collect records for "listing" or "modify" these tables. Support the permissions of each type of record (@see t3lib_userAuthGroup::check).
	 * @param	integer $language Select specific language only
	 * @return	array Array of all records uids etc. First key is table name, second key incremental integer. Records are associative arrays with uid and t3ver_oidfields. The pid of the online record is found as "livepid" the pid of the offline record is found in "wspid"
	 */
	public function selectVersionsInWorkspace($wsid, $filter = 0, $stage = -99, $pageId = -1, $recursionLevel = 0, $selectionType = 'tables_select', $language = NULL) {
		if ($stage == -99) {
			$stage = $this->stage;
		}

		return parent::selectVersionsInWorkspace($wsid, $filter, $stage, $pageId, $recursionLevel, $selectionType, $language);
	}
}

?>