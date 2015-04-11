<?php
namespace OliverHader\IrreWorkspaces\Hook;

/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Oliver Hader <oliver.hader@typo3.org>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Workspaces\Domain\Model\CombinedRecord;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class ReductionHook implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \OliverHader\IrreWorkspaces\Service\Deviation\RecordService
	 */
	protected $deviationRecordService;

	/**
	 * @param array $parameters
	 */
	public function countVersionsOfRecordsOnPage(array $parameters) {
		if (empty($parameters['versions'])) {
			return;
		}

		foreach ($parameters['versions'] as $tableName => $versions) {
			foreach ($versions as $versionIndex => $version) {
				$combinedRecord = CombinedRecord::create(
					$tableName,
					$version['live_uid'],
					$version['offline_uid']
				);

				$reduceElement = (
					$this->getDeviationRecordService()->isModified($combinedRecord) &&
					$this->getDeviationRecordService()->hasDeviation($combinedRecord) === FALSE
				);

				// Reduce submitted version array
				if ($reduceElement) {
					unset($parameters['versions'][$tableName][$versionIndex]);
				// Break, since one positive match is enough to stop reduction
				} else {
					return;
				}
			}
			// Remove table from versions array if it's empty
			if (empty($parameters['versions'][$tableName])) {
				unset($parameters['versions'][$tableName]);
			}
		}
	}

	/**
	 * @param array $parameters
	 */
	public function hasPageVersions(array $parameters) {
		if (empty($parameters['hasVersions'])) {
			return;
		}

		$pageId = $parameters['pageId'];
		$workspaceId = $parameters['workspaceId'];
		$pagesWithVersions = $this->getWorkspaceUtility()->getPagesWithVersions($workspaceId);

		foreach ($GLOBALS['TCA'] as $tableName => $tableConfiguration) {
			if (empty($pagesWithVersions[$tableName])) {
				continue;
			}

			$versions = $this->getVersions($workspaceId, $pageId, $tableName);

			if (empty($versions)) {
				continue;
			}

			foreach ($versions as $version) {
				$combinedRecord = CombinedRecord::create(
					$tableName,
					$version['live_uid'],
					$version['offline_uid']
				);

				$reduceElement = (
					$this->getDeviationRecordService()->isModified($combinedRecord) &&
					$this->getDeviationRecordService()->hasDeviation($combinedRecord) === FALSE
				);

				// If element cannot be reduced, there's at least one version on the page
				if (!$reduceElement) {
					return;
				}
			}
		}

		// If all elements could be reduced, there's no version on the page
		$parameters['hasVersion'] = FALSE;
	}

	/**
	 * @param int $workspaceId
	 * @param int $pageId
	 * @param string $tableName
	 * @return array
	 */
	protected function getVersions($workspaceId, $pageId, $tableName) {
		$joinStatement = 'A.t3ver_oid=B.uid';
		// Consider records that are moved to a different page
		if (BackendUtility::isTableMovePlaceholderAware($tableName)) {
			$movePointer = new VersionState(VersionState::MOVE_POINTER);
			$joinStatement = '(A.t3ver_oid=B.uid AND A.t3ver_state<>' . $movePointer
				. ' OR A.t3ver_oid=B.t3ver_move_id AND A.t3ver_state=' . $movePointer . ')';
		}
		// Select all records from this table in the database from the workspace
		// This joins the online version with the offline version as tables A and B
		$rows = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'B.uid as live_uid, A.uid as offline_uid',
			$tableName . ' A,' . $tableName . ' B',
			'A.pid=-1' . ' AND B.pid=' . (int)$pageId
				. ' AND A.t3ver_wsid=' . (int)$workspaceId . ' AND ' . $joinStatement
				. BackendUtility::deleteClause($tableName, 'A') . BackendUtility::deleteClause($tableName, 'B')
		);

		return $rows;
	}

	/**
	 * @return \OliverHader\IrreWorkspaces\Service\Deviation\RecordService
	 */
	protected function getDeviationRecordService() {
		if (!isset($this->deviationRecordService)) {
			$this->deviationRecordService = $this->getObjectManager()->get(
				'OliverHader\\IrreWorkspaces\\Service\\Deviation\\RecordService'
			);
		}
		return $this->deviationRecordService;
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Version\Utility\WorkspacesUtility
	 */
	protected function getWorkspaceUtility() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Version\\Utility\\WorkspacesUtility'
		);
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Extbase\\Object\\ObjectManager'
		);
	}


}