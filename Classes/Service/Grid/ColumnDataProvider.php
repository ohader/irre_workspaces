<?php
namespace OliverHader\IrreWorkspaces\Service\Grid;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Workspaces\Service\HistoryService;
use TYPO3\CMS\Workspaces\ColumnDataProviderInterface;
use TYPO3\CMS\Workspaces\Domain\Model\CombinedRecord;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class ColumnDataProvider implements SingletonInterface, ColumnDataProviderInterface {

	/**
	 * @var array
	 */
	protected $editors;

	/**
	 * @return array
	 */
	public function getDefinition() {
		return array('type' => 'string');
	}

	/**
	 * @param CombinedRecord $combinedRecord
	 * @return string|NULL
	 */
	public function getData(CombinedRecord $combinedRecord) {
		// Fetch last editor from record history
		$lastEditor = $this->getLastEditorFromHistory(
			$combinedRecord->getTable(),
			$combinedRecord->getLiveId()
		);

		// Use creator field (if defined in TCA)
		if ($lastEditor === NULL) {
			if (!empty($GLOBALS['TCA'][$combinedRecord->getTable()]['ctrl']['cruser_id'])) {
				$creatorFieldName = $GLOBALS['TCA'][$combinedRecord->getTable()]['ctrl']['cruser_id'];
				$versionRow = $combinedRecord->getVersionRecord()->getRow();
				$lastEditor = $this->getEditorName($versionRow[$creatorFieldName]);
			}
		}

		return $lastEditor;
	}

	/**
	 * @param string $tableName
	 * @param integer $id
	 * @return NULL|string
	 */
	protected function getLastEditorFromHistory($tableName, $id) {
		$lastEditor = NULL;
		$historyEntries = $this->getHistoryService()->getHistory($tableName, $id);
		if (!empty($historyEntries)) {
			$lastEditor = $historyEntries[0]['user'];
		}
		return $lastEditor;
	}

	/**
	 * @param int $editorId
	 * @return NULL|string
	 */
	protected function getEditorName($editorId) {
		$editorName = NULL;
		$editorId = (string)$editorId;
		if (!isset($this->editors)) {
			$this->editors = array();
			$records = $this->getDatabaseConnection()->exec_SELECTgetRows(
				'uid,username',
				'be_users',
				'pid=0',
				'',
				'',
				'',
				'uid'
			);
			if (!empty($records)) {
				$this->editors = $records;
			}
		}
		if (!empty($this->editors[$editorId])) {
			$editorName = $this->editors[$editorId]['username'];
		}
		return $editorName;
	}

	/**
	 * @return HistoryService
	 */
	protected function getHistoryService() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Workspaces\\Service\\HistoryService'
		);
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}