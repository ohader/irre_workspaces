<?php
namespace OliverHader\IrreWorkspaces\Domain\Model\Dependency;

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

use TYPO3\CMS\Version\Dependency\ElementEntity;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class IncompleteStructure {
	/**
	 * @var ElementEntity
	 */
	protected $outerMostParent;

	/**
	 * @var array|ElementEntity[]
	 * @deprecated Unused
	 */
	protected $dependentElements;

	/**
	 * @var array|ElementEntity[]
	 */
	protected $intersectingElements;

	/**
	 * @var array|ElementEntity[]
	 */
	protected $differentElements;

	/**
	 * @param ElementEntity $outerMostParent
	 * @param array $intersectingElements
	 * @param array $differentElements
	 * @return IncompleteStructure
	 */
	static public function create(ElementEntity $outerMostParent, array $intersectingElements, array $differentElements) {
		/** @var $incompleteStructure IncompleteStructure */
		$incompleteStructure = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure');
		$incompleteStructure->setOuterMostParent($outerMostParent);
		$incompleteStructure->setIntersectingElements($intersectingElements);
		$incompleteStructure->setDifferentElements($differentElements);
		return $incompleteStructure;
	}

	/**
	 * @param ElementEntity $outerMostParent
	 */
	public function setOuterMostParent(ElementEntity $outerMostParent) {
		$this->outerMostParent = $outerMostParent;
	}

	/**
	 * @return ElementEntity
	 */
	public function getOuterMostParent() {
		return $this->outerMostParent;
	}

	/**
	 * @param array|ElementEntity[] $dependentElements
	 * @deprecated Unused
	 */
	public function setDependentElements(array $dependentElements) {
		$this->dependentElements = $dependentElements;
	}

	/**
	 * @return array|ElementEntity[]
	 * @deprecated Unused
	 */
	public function getDependentElements() {
		return $this->dependentElements;
	}

	/**
	 * @param array|ElementEntity[] $intersectingElements
	 */
	public function setIntersectingElements(array $intersectingElements) {
		$this->intersectingElements = $intersectingElements;
	}

	/**
	 * @return array|ElementEntity[]
	 */
	public function getIntersectingElements() {
		return $this->intersectingElements;
	}

	/**
	 * @param string $identifier
	 * @return NULL|ElementEntity
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
	 * @param ElementEntity $element
	 * @return NULL|ElementEntity
	 */
	public function findIntersectingElement(ElementEntity $element) {
		$result = NULL;

		foreach ($this->intersectingElements as $intersectingElement) {
			if ($intersectingElement->getTable() === $element->getTable() && $intersectingElement->getId() == $element->getId()) {
				$result = $intersectingElement;
				break;
			}
		}

		return $result;
	}

	/**
	 * @param string $identifier
	 * @return boolean
	 */
	public function hasIntersectingElement($identifier) {
		return ($this->getIntersectingElement($identifier) !== NULL);
	}

	/**
	 * @param array|ElementEntity[] $differentElements
	 */
	public function setDifferentElements(array $differentElements) {
		$this->differentElements = $differentElements;
	}

	/**
	 * @return array|ElementEntity[]
	 */
	public function getDifferentElements() {
		return $this->differentElements;
	}

	/**
	 * @param string $identifier
	 * @return NULL|ElementEntity
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
	 * @param ElementEntity $element
	 * @return NULL|ElementEntity
	 */
	public function findDifferentElement(ElementEntity $element) {
		$result = NULL;

		foreach ($this->differentElements as $differentElement) {
			if ($differentElement->getTable() === $element->getTable() && $differentElement->getId() == $element->getId()) {
				$result = $differentElement;
				break;
			}
		}

		return $result;
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