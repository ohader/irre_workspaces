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
use TYPO3\CMS\Workspaces\Domain\Model\CombinedRecord;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class ReductionService implements SingletonInterface {

	/**
	 * @var \OliverHader\IrreWorkspaces\Service\Deviation\RecordService
	 * @inject
	 */
	protected $deviationRecordService;

	/**
	 * @param array $dataArray
	 * @return array
	 */
	public function reIndex(array $dataArray) {
		return array_merge($dataArray, array());
	}

	/**
	 * @param array $dataArray
	 * @return array
	 */
	public function reduce(array $dataArray) {
		foreach ($dataArray as $index => $dataElement) {
			$combinedRecord = CombinedRecord::create(
				$dataElement['table'],
				$dataElement['t3ver_oid'],
				$dataElement['uid']
			);

			$reduceElement = (
				$this->deviationRecordService->isModified($combinedRecord) &&
				$this->deviationRecordService->hasDeviation($combinedRecord) === FALSE
			);

			if ($reduceElement) {
				unset($dataArray[$index]);
			}
		}

		return $this->reIndex($dataArray);
	}

	/**
	 * @param array $dataArray
	 * @return array
	 */
	public function purge(array $dataArray) {
		// @todo Combine modification with deviation service, $this->reduceDataArray()
		/*
		foreach ($dataArray as $key => $dataElement) {
			if (isset($dataElement[self::GridColumn_Modification]) && $dataElement[self::GridColumn_Modification] === FALSE) {
				unset($dataArray[$key]);
			}
		}
		*/

		return $this->reIndex($dataArray);
	}

}