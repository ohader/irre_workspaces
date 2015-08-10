<?php
namespace OliverHader\IrreWorkspaces\Service;

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
use OliverHader\IrreWorkspaces\Cache\ReductionCache;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
abstract class AbstractReductionService implements SingletonInterface {

	/**
	 * @var \OliverHader\IrreWorkspaces\Service\Deviation\RecordService
	 * @inject
	 */
	protected $deviationRecordService;

	/**
	 * @param array $dataArray
	 * @return array
	 */
	public function reIndex(array $data) {
		return array_merge($data, array());
	}

	abstract public function reduce(array $data);

	abstract public function purge(array $data);

	/**
	 * Determines whether a record is reducible by invoking a cache.
	 * The cache stores integer values 0 or 1 - since the Caching Framework
	 * returns FALSE, if a cache entry is not found...
	 *
	 * @param CombinedRecord $combinedRecord
	 * @return bool
	 */
	protected function isReducible(CombinedRecord $combinedRecord) {
		$identifier = md5($combinedRecord->getVersionRecord()->getIdentifier());
		$cacheValue = ReductionCache::create()->get($identifier);

		if ($cacheValue !== FALSE) {
			$isReducible = ($cacheValue === 1);
		} else {
			$isReducible = (
				$this->deviationRecordService->isModified($combinedRecord) &&
				$this->deviationRecordService->hasDeviation($combinedRecord) === FALSE
			);

			$versionRow = $combinedRecord->getVersionRecord()->getRow();
			$cacheValue = ($isReducible ? 1 : 0);
			$cacheTags = array('workspace_' . $versionRow['t3ver_wsid']);
			ReductionCache::create()->set($identifier, $cacheValue, $cacheTags);
		}

		return $isReducible;
	}

}