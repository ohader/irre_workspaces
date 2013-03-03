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
class Tx_IrreWorkspaces_Service_StageService implements t3lib_Singleton {
	const PATH_LanguageFile = 'LLL:EXT:workspaces/Resources/Private/Language/locallang.xml';

	/**
	 * @return array
	 */
	public function getAllowedStages() {
		$allowedStages = array();

		$stages = $this->getCurrentStages();

		if (is_array($stages)) {
			if ($this->getBackendUser()->isAdmin()) {
				$allowedStages = $stages;
			} else {
				foreach ($stages as $stage) {
					if ($this->getBackendUser()->workspaceCheckStageForCurrent($stage['uid'])) {
						$allowedStages[$stage['uid']] = $stage;
					}
				}

				krsort($allowedStages);
				$allowedStages = array_values($allowedStages);
			}
		}

		return $allowedStages;
	}

	/**
	 * @return array
	 */
	protected function getCurrentStages() {
		$stages = array();

		$stages[] = array(
			'uid' => Tx_Workspaces_Service_Stages::STAGE_EDIT_ID,
			'title' => $this->getLanguage()->sL(
				'LLL:EXT:lang/locallang_mod_user_ws.xml:stage_editing'
			),
		);

		foreach($this->getStageRecords() as $stageRecord) {
			$stages[] = array(
				'uid' => $stageRecord['uid'],
				'title' => $stageRecord['title'],
			);
		}

		$stages[] = array(
			'uid' => Tx_Workspaces_Service_Stages::STAGE_PUBLISH_ID,
			'title' => $this->getLanguage()->sL(
				'LLL:EXT:workspaces/Resources/Private/Language/locallang_mod.xml:stage_ready_to_publish'
			),
		);

		return $stages;
	}

	/**
	 * @param integer $workspaceId
	 * @return array|NULL
	 */
	protected function getStageRecords($workspaceId = NULL) {
		if ($workspaceId === NULL) {
			$workspaceId = $this->getCurrentWorkspaceId();
		}

		$workspaceId = (int) $workspaceId;

		$stageRecords = $this->getDatabase()->exec_SELECTgetRows(
			'*',
			Tx_Workspaces_Service_Stages::TABLE_STAGE,
			'deleted=0 AND parentid=' . $workspaceId .
				' AND parenttable=' . $this->getDatabase()->fullQuoteStr('sys_workspace', Tx_Workspaces_Service_Stages::TABLE_STAGE),
			'',
			'sorting',
			'',
			'uid'
		);

		return $stageRecords;
	}

	/**
	 * @param integer $workspaceId
	 * @return array
	 */
	protected function getWorkspaceRecord($workspaceId = NULL) {
		if ($workspaceId === NULL) {
			$workspaceId = $this->getCurrentWorkspaceId();
		}

		$workspaceId = (int) $workspaceId;

		return t3lib_BEfunc::getRecord(
			'sys_workspace',
			$workspaceId
		);
	}

	/**
	 * @return integer
	 */
	protected function getCurrentWorkspaceId() {
		return (int) $this->getBackendUser()->workspace;
	}

	/**
	 * @return t3lib_beUserAuth
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return language
	 */
	protected function getLanguage() {
		return $GLOBALS['LANG'];
	}
}

?>