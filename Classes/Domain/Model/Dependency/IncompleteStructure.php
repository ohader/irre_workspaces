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
class Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure {
	/**
	 * @var t3lib_utility_Dependency_Element
	 */
	protected $outerMostParent;

	/**
	 * @var array|t3lib_utility_Dependency_Element[]
	 */
	protected $dependentElements;

	/**
	 * @var array|t3lib_utility_Dependency_Element[]
	 */
	protected $intersectingElements;

	/**
	 * @var array|t3lib_utility_Dependency_Element[]
	 */
	protected $differentElements;

	/**
	 * @param t3lib_utility_Dependency_Element $outerMostParent
	 * @param array $intersectingElements
	 * @param array $differentElements
	 * @return Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure
	 */
	static public function create(t3lib_utility_Dependency_Element $outerMostParent, array $intersectingElements, array $differentElements) {
		/** @var $incompleteStructure Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure */
		$incompleteStructure = t3lib_div::makeInstance('Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure');
		$incompleteStructure->setOuterMostParent($outerMostParent);
		$incompleteStructure->setIntersectingElements($intersectingElements);
		$incompleteStructure->setDifferentElements($differentElements);
		return $incompleteStructure;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $outerMostParent
	 */
	public function setOuterMostParent(t3lib_utility_Dependency_Element $outerMostParent) {
		$this->outerMostParent = $outerMostParent;
	}

	/**
	 * @return t3lib_utility_Dependency_Element
	 */
	public function getOuterMostParent() {
		return $this->outerMostParent;
	}

	/**
	 * @param array|t3lib_utility_Dependency_Element[] $dependentElements
	 */
	public function setDependentElements(array $dependentElements) {
		$this->dependentElements = $dependentElements;
	}

	/**
	 * @return array|t3lib_utility_Dependency_Element[]
	 */
	public function getDependentElements() {
		return $this->dependentElements;
	}

	/**
	 * @param array|t3lib_utility_Dependency_Element[] $intersectingElements
	 */
	public function setIntersectingElements(array $intersectingElements) {
		$this->intersectingElements = $intersectingElements;
	}

	/**
	 * @return array|t3lib_utility_Dependency_Element[]
	 */
	public function getIntersectingElements() {
		return $this->intersectingElements;
	}

	/**
	 * @param string $identifier
	 * @return NULL|t3lib_utility_Dependency_Element
	 */
	public function getIntersectingElement($identifier) {
		$element = NULL;

		foreach ($this->intersectingElements as $intersectingElement) {
			if ($intersectingElement->__toString() === $identifier) {
				$element = $intersectingElement;
				break;
			}
		}

		return $element;
	}

	/**
	 * @param string $identifier
	 * @return boolean
	 */
	public function hasIntersectingElement($identifier) {
		return ($this->getIntersectingElement($identifier) !== NULL);
	}

	/**
	 * @param array|t3lib_utility_Dependency_Element[] $differentElements
	 */
	public function setDifferentElements(array $differentElements) {
		$this->differentElements = $differentElements;
	}

	/**
	 * @return array|t3lib_utility_Dependency_Element[]
	 */
	public function getDifferentElements() {
		return $this->differentElements;
	}

	/**
	 * @param string $identifier
	 * @return NULL|t3lib_utility_Dependency_Element
	 */
	public function getDifferentElement($identifier) {
		$element = NULL;

		foreach ($this->differentElements as $differentElement) {
			if ($differentElement->__toString() === $identifier) {
				$element = $differentElement;
				break;
			}
		}

		return $element;
	}

	/**
	 * @param string $identifier
	 * @return boolean
	 */
	public function hasDifferentElement($identifier) {
		return ($this->getDifferentElement($identifier) !== NULL);
	}
}

?>