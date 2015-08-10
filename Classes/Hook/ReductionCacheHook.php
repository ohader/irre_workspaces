<?php
namespace OliverHader\IrreWorkspaces\Hook;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015 Oliver Hader <oliver.hader@typo3.org>
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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use OliverHader\IrreWorkspaces\Cache\ReductionCache;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class ReductionCacheHook implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $collection = array();

	/**
	 * Collects data modifications.
	 *
	 * @param string $status
	 * @param string $table
	 * @param string|int $id
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id) {
		if (!$this->isWorkspace()|| !MathUtility::canBeInterpretedAsInteger($id)) {
			return;
		}

		$this->collect($table, $id);
	}

	/**
	 * Collection data commands.
	 *
	 * @param string $command
	 * @param string $table
	 * @param string|int $id
	 * @param mixed $value
	 */
	public function processCmdmap_postProcess($command, $table, $id, $value) {
		if (!$this->isWorkspace() || !MathUtility::canBeInterpretedAsInteger($id)) {
			return;
		}

		$this->collect($table, $id);

		if ($command === 'version' && !empty($value['action'])) {
			if ($value['action'] === 'swap') {
				$this->collect($table, $value['swapWith']);
			}
		}
	}

	/**
	 * Processes remaining collected items.
	 */
	public function processDatamap_afterAllOperations() {
		$this->purgeCache();
	}

	/**
	 * Processes remaining collected items.
	 */
	public function processCmdmap_afterFinish() {
		$this->purgeCache();
	}

	/**
	 * @param string $tableName
	 * @param int $id
	 */
	protected function collect($tableName, $id) {
		if (!isset($this->collection[$tableName])) {
			$this->collection[$tableName] = array();
		}
		if (!in_array($id, $this->collection[$tableName])) {
			$this->collection[$tableName][] = $id;
		}
	}

	/**
	 * Purges cache entries for collected items.
	 */
	protected function purgeCache() {
		foreach ($this->collection as $tableName => $ids) {
			$versionIds = $this->getVersionIds($tableName, $ids);
			foreach ($versionIds as $versionId) {
				$identifier = md5($tableName . ':' . $versionId);
				ReductionCache::create()->remove($identifier);
			}
		}

		$this->collection = array();
	}

	/**
	 * @param string $tableName
	 * @param array $ids
	 * @return array|int[]
	 */
	protected function getVersionIds($tableName, array $ids) {
		$ids = $this->getDatabaseConnection()->cleanIntArray($ids);
		$ids = array_combine($ids, $ids);

		$versions = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'uid,t3ver_oid,t3ver_state',
			$tableName,
			'pid=-1 AND t3ver_oid IN (' . implode(',', $ids) . ') AND t3ver_wsid<>0',
			'',
			't3ver_state DESC'
		);

		if (!empty($versions)) {
			foreach ($versions as $version) {
				$versionId = $version['uid'];
				$liveId = $version['t3ver_oid'];

				if (isset($ids[$liveId])) {
					unset($ids[$liveId]);
				}
				if (!isset($ids[$versionId])) {
					$ids[$versionId] = $versionId;
				}
			}
		}

		return array_values($ids);
	}

	/**
	 * @return bool
	 */
	protected function isWorkspace() {
		return ((int)$this->getWorkspaceService()->getCurrentWorkspace() !== 0);
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Workspaces\Service\WorkspaceService
	 */
	protected function getWorkspaceService() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Workspaces\\Service\\WorkspaceService'
		);
	}

}