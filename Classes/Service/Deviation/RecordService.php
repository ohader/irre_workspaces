<?php
namespace OliverHader\IrreWorkspaces\Service\Deviation;

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
class RecordService implements SingletonInterface {

	/**
	 * @var \OliverHader\IrreWorkspaces\Service\Deviation\FieldService
	 * @inject
	 */
	protected $deviationFieldService;

	/**
	 * @param CombinedRecord $combinedRecord
	 * @return bool
	 */
	public function hasDeviation(CombinedRecord $combinedRecord) {
		$result = FALSE;

		if ($this->isModified($combinedRecord) === FALSE) {
			$result = TRUE;
		} else {
			foreach ($this->getRecordFields($combinedRecord) as $field) {
				if ($this->isDeviation($combinedRecord, $field)) {
					$result = TRUE;
				}
			}
		}

		return $result;
	}

	/**
	 * @param CombinedRecord $combinedRecord
	 * @return bool
	 */
	public function isModified(CombinedRecord $combinedRecord) {
		return $this->deviationFieldService->isModified(
			$combinedRecord->getVersionRecord()->getRow()
		);
	}

	/**
	 * @param CombinedRecord $combinedRecord
	 * @param string $field
	 * @return bool
	 */
	protected function isDeviation(CombinedRecord $combinedRecord, $field) {
		return $this->deviationFieldService->isDeviation(
			$combinedRecord->getTable(),
			$field,
			$combinedRecord->getLiveRecord()->getRow(),
			$combinedRecord->getVersionRecord()->getRow()
		);
	}

	/**
	 * @param CombinedRecord $combinedRecord
	 * @return array
	 */
	protected function getRecordFields(CombinedRecord $combinedRecord) {
		return array_keys($combinedRecord->getLiveRecord()->getRow());
	}

}

?>