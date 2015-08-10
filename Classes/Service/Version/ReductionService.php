<?php
namespace OliverHader\IrreWorkspaces\Service\Version;

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

use TYPO3\CMS\Workspaces\Domain\Model\CombinedRecord;
use OliverHader\IrreWorkspaces\Service\AbstractReductionService;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class ReductionService extends AbstractReductionService {

	/**
	 * @param array $data
	 * @return array
	 */
	public function reduce(array $data) {
		foreach ($data as $tableName => $versionRows) {
			foreach ($versionRows as $index => $versionRow) {
				$combinedRecord = CombinedRecord::create(
					$tableName,
					$versionRow['t3ver_oid'],
					$versionRow['uid']
				);

				if ($this->isReducible($combinedRecord)) {
					unset($data[$tableName][$index]);
				}
			}

			if (count($data[$tableName]) === 0) {
				unset($data[$tableName]);
			} else {
				$data[$tableName] = $this->reIndex($data[$tableName]);
			}
		}

		return $data;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function purge(array $data) {
		return $this->reIndex($data);
	}

}