<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Oliver Hader <oliver.hader@typo3.org>
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
abstract class Tx_IrreWorkspaces_Service_Action_AbstractActionService implements t3lib_Singleton {
	/**
	 * @var Tx_Workspaces_Service_Stages
	 */
	protected $workspacesStagesService;

	/**
	 * @param string $parentTable
	 * @param integer $parentId
	 * @param array $parentConfiguration
	 * @return t3lib_loadDBGroup
	 */
	protected function getReferenceCollection($parentTable, $parentId, array $parentConfiguration) {
		/** @var $referenceCollection t3lib_loadDBGroup */
		$referenceCollection = t3lib_div::makeInstance('t3lib_loadDBGroup');
		$referenceCollection->start(
			'',
			$parentConfiguration['foreign_table'],
			$parentConfiguration['MM'],
			$parentId,
			$parentTable,
			$parentConfiguration
		);
		return $referenceCollection;
	}

	/**
	 * @return Tx_Workspaces_Service_Stages
	 */
	protected function getWorkspacesStagesService() {
		if (!isset($this->workspacesStagesService)) {
			$this->workspacesStagesService = t3lib_div::makeInstance('Tx_Workspaces_Service_Stages');
		}
		return $this->workspacesStagesService;
	}

	/**
	 * @return language
	 */
	protected function getLanguage() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return t3lib_beUserAuth
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}
}

?>