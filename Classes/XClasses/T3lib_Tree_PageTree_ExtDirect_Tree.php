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
class ux_t3lib_tree_pagetree_extdirect_Tree extends t3lib_tree_pagetree_extdirect_Tree {
	/**
	 * Fetches the next tree level - but overrides node-limit.
	 *
	 * @param integer $nodeId
	 * @param stdClass $nodeData
	 * @return array
	 */
	public function getNextTreeLevel($nodeId, $nodeData) {
		$this->initDataProvider(FALSE, 999999);
		return parent::getNextTreeLevel($nodeId, $nodeData);
	}

	/**
	 * Sets the data provider
	 *
	 * @param boolean $override
	 * @param integer $nodeLimit
	 * @return void
	 */
	protected function initDataProvider($override = FALSE, $nodeLimit = NULL) {
		if ($override || !isset($this->dataProvider)) {
			/** @var $dataProvider t3lib_tree_pagetree_DataProvider */
			$dataProvider = t3lib_div::makeInstance('t3lib_tree_pagetree_DataProvider', $nodeLimit);
			$this->setDataProvider($dataProvider);
		}
	}
}

?>