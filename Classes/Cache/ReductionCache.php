<?php
namespace OliverHader\IrreWorkspaces\Cache;

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

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class ReductionCache implements SingletonInterface {

	const CACHE_Name = 'irre_workspaces_reduction';

	/**
	 * @return ReductionCache
	 */
	static public function create() {
		return GeneralUtility::makeInstance(__CLASS__);
	}

	/**
	 * @param string $entryIdentifier
	 * @return mixed
	 */
	public function get($entryIdentifier) {
		return $this->getCache()->get($entryIdentifier);
	}

	/**
	 * @param string $entryIdentifier
	 * @param mixed $data
	 * @param array $tags
	 * @param int $lifetime
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		$this->getCache()->set($entryIdentifier, $data, $tags, $lifetime);
	}

	/**
	 * @param string $entryIdentifier
	 * @return bool
	 */
	public function remove($entryIdentifier) {
		return $this->getCache()->remove($entryIdentifier);
	}

	/**
	 * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
	 */
	protected function getCache() {
		return $this->getCacheManager()->getCache(static::CACHE_Name);
	}

	/**
	 * @return \TYPO3\CMS\Core\Cache\CacheManager
	 */
	protected function getCacheManager() {
		return GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Core\\Cache\\CacheManager'
		);
	}

}