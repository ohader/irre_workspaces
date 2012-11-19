<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Oliver Hader <oliver.hader@typo3.org>
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

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class Tx_IrreWorkspaces_Hooks_PreviewHook implements t3lib_Singleton {
	/**
	 * @var array
	 */
	protected $pageTsConfig = array();

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function getSingleRecordLink(array $parameters) {
		$singleRecordLink = '';
		$record = $this->getRecord($parameters);

		if (NULL !== $previewPageId = $this->getPreviewPageId($record['pid'], $parameters['table'])) {
			$singleRecordLink = t3lib_BEfunc::viewOnClick($previewPageId);
		}

		return $singleRecordLink;
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	protected function getRecord(array $parameters) {
		$record = $parameters['record'];

		if (!is_array($record)) {
			$record = t3lib_BEfunc::getLiveVersionOfRecord($parameters['table'], $parameters['uid']);
		}

		return $record;
	}

	/**
	 * @param integer $pageId
	 * @param string $table
	 * @return integer|NULL
	 */
	protected function getPreviewPageId($pageId, $table) {
		$previewPageId = NULL;

		if ($pageTsConfig = $this->getPageTsConfig($pageId)) {
			if (NULL !== $result = $this->getPath($pageTsConfig, 'previewPageId.' . $table)) {
				$previewPageId = (int) $result;
			} elseif (NULL !== $result = $this->getPath($pageTsConfig, 'previewPageId')) {
				$previewPageId = (int) $result;
			}
		}

		return $previewPageId;
	}

	/**
	 * @param integer $pageId
	 * @return array|NULL
	 */
	protected function getPageTsConfig($pageId) {
		if (!isset($this->pageTsConfig[$pageId])) {
			$pageTsConfig = t3lib_BEfunc::getPagesTSconfig($pageId);
			$this->pageTsConfig[$pageId] = $this->getPath($pageTsConfig, 'options.workspaces.');
		}
		return $this->pageTsConfig[$pageId];
	}

	/**
	 * @param array $array
	 * @param string $path (e.g. "options.workspaces.whatever")
	 * @return array|null
	 */
	protected function getPath(array $array, $path) {
		$steps = t3lib_div::trimExplode('/',  str_replace('.', './', $path), TRUE);

		foreach ($steps as $step) {
			if (isset($array[$step])) {
				$array = $array[$step];
			} else {
				$array = NULL;
			}
		}

		return $array;
	}
}

?>