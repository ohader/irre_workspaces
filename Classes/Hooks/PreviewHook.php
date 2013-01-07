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

		$uid = $parameters['uid'];
		$table = $parameters['table'];
		$liveRecord = $parameters['liveRecord'];

		if (empty($liveRecord)) {
			$liveRecord = t3lib_BEfunc::getLiveVersionOfRecord($table, $uid);
		}

		if (NULL !== $previewPageId = $this->getPreviewPageId($liveRecord['pid'], $table, $liveRecord)) {
			$singleRecordLink = t3lib_BEfunc::viewOnClick($previewPageId);
		}

		return $singleRecordLink;
	}

	/**
	 * @param integer $pageId
	 * @param string $table
	 * @param array $record
	 * @return integer|NULL
	 */
	protected function getPreviewPageId($pageId, $table, array $record) {
		$previewPageId = NULL;

		if ($pageTsConfig = $this->getPageTsConfig($pageId)) {
			if (NULL !== $result = $this->getPath($pageTsConfig, 'previewPageId.' . $table)) {
				$previewPageId = $result;
			} elseif (NULL !== $result = $this->getPath($pageTsConfig, 'previewPageId')) {
				$previewPageId = $result;
			}

			list($key, $value) = t3lib_div::trimExplode(':', $previewPageId, FALSE, 2);

			if ($key === 'field') {
				$previewPageId = (int) $record[$value];
			} else {
				$previewPageId = (int) $previewPageId;
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