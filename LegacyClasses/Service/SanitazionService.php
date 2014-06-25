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
class Tx_IrreWorkspaces_Service_SanitazionService {
	/**
	 * @var array
	 */
	protected $recordsCache = array();

	/**
	 * @var t3lib_TCEmain
	 */
	protected $tceMain;

	/**
	 * @var array
	 */
	protected $dataMap;

	/**
	 * @var t3lib_utility_Dependency_Element
	 */
	protected $outerMostParent;

	/**
	 * @var Tx_IrreWorkspaces_Service_ComparisonService
	 */
	protected $comparisonService;

	/**
	 * @param t3lib_TCEmain $tceMain
	 * @param t3lib_utility_Dependency_Element $outerMostParent
	 */
	public function __construct(t3lib_TCEmain $tceMain, t3lib_utility_Dependency_Element $outerMostParent) {
		$this->tceMain = $tceMain;
		$this->dataMap = $tceMain->datamap;
		$this->outerMostParent = $outerMostParent;
	}

	/**
	 * Sanitizes the current outer most parent dependency.
	 */
	public function sanitize() {
		$this->sanitizeElement($this->outerMostParent);
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 */
	protected function sanitizeElement(t3lib_utility_Dependency_Element $element) {
		if ($this->getComparisonService()->hasDifferences($element) === FALSE) {
			$this->sanitizeChildren($element->getChildren());
			$this->purgeDataMap($element);
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Reference[] $children
	 * @return boolean
	 */
	protected function sanitizeChildren(array $children) {
		foreach ($children as $child) {
			$this->sanitizeElement($child->getElement());
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 */
	protected function purgeDataMap(t3lib_utility_Dependency_Element $element) {
		if (isset($this->tceMain->datamap[$element->getTable()][$element->getId()])) {
			unset($this->tceMain->datamap[$element->getTable()][$element->getId()]);
		}
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_ComparisonService
	 */
	protected function getComparisonService() {
		if (!isset($this->comparisonService)) {
			$this->comparisonService = t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_ComparisonService', $this->tceMain);
		}

		return $this->comparisonService;
	}
}

?>