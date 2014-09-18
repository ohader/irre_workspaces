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
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Extbase\\Object\\ObjectManager'
		);
	}


}