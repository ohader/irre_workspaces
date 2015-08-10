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
		foreach ($data as $index => $dataElement) {
			$combinedRecord = CombinedRecord::create(
				$dataElement['table'],
				$dataElement['t3ver_oid'],
				$dataElement['uid']
			);

			if ($this->isReducible($combinedRecord)) {
				unset($data[$index]);
			}
		}

		return $this->reIndex($data);
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function purge(array $data) {
		// @todo Combine modification with deviation service, $this->reduceDataArray()
		/*
		foreach ($dataArray as $key => $dataElement) {
			if (isset($dataElement[self::GridColumn_Modification]) && $dataElement[self::GridColumn_Modification] === FALSE) {
				unset($dataArray[$key]);
			}
		}
		*/

		return $this->reIndex($data);
	}

}