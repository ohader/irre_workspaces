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
class Ux_Tx_Workspaces_Service_GridData extends Tx_Workspaces_Service_GridData {
	const GridColumn_Collection = 'Tx_IrreWorkspaces_Collection';

	/**
	 * @var t3lib_TCEmain
	 */
	protected $tceMainHelper;

	/**
	 * @param array $dataArray
	 */
	public function setDataArray(array $dataArray) {
		$this->dataArray = $dataArray;
	}

	/**
	 * Generates grid list array from given versions.
	 *
	 * @param array $versions
	 * @param string $filterTxt
	 * @return void
	 */
	protected function generateDataArray(array $versions, $filterTxt) {
		parent::generateDataArray($versions, $filterTxt);

		$this->extendDataArray();
		$this->resolveDataDependencies();
	}

	/**
	 * Gets the data array by considering the page to be shown in the grid view.
	 *
	 * @param integer $start
	 * @param integer $limit
	 * @return array
	 */
	protected function getDataArray($start, $limit) {
		$dataArrayPart = parent::getDataArray($start, $limit);

		$lastIndex = count($dataArrayPart) - 1;

		if ($this->hasDependentCollectionIdentifier($lastIndex)) {
			for ($i = $lastIndex + 1; $i < count($this->dataArray) && $this->hasDependentCollectionIdentifier($i); $i++) {
				$dataArrayPart[] = $this->dataArray[$i];
			}
		}

		return $dataArrayPart;
	}


	protected function getDataArrayFromCache() {
		return FALSE;
	}

	/**
	 * Resolves dependencies of nested structures
	 * and sort data elements considering these dependencies.
	 *
	 * @return void
	 */
	protected function resolveDataDependencies() {
		$dependency = $this->getDependencyUtility();

		foreach ($this->dataArray as $dataElement) {
			$dependency->addElement($dataElement['table'], $dataElement['uid']);
		}

		/** @var $outerMostParents t3lib_utility_Dependency_Element[] */
		$outerMostParents = $dependency->getOuterMostParents();

		if ($outerMostParents) {
			$dataArray = $this->getDataArrayWithIdentifier();
			$nestedDataArray = array();
			$collectionIdentifier = 0;

			// For each outer most parent, get all nested child elements:
			foreach ($outerMostParents as $outerMostParent) {
				$nestedElements = array();
				$parentIdentifier = $outerMostParent->__toString();

				$this->setCollectionIdentifier(
					$dataArray[$parentIdentifier],
					++$collectionIdentifier
				);

				/** @var $child t3lib_utility_Dependency_Element */
				foreach ($outerMostParent->getNestedChildren() as $child) {
					$childIdentifier = $child->__toString();

					$this->setCollectionIdentifier(
						$dataArray[$childIdentifier],
						$collectionIdentifier
					);

					$nestedElements[] = $dataArray[$childIdentifier];
					unset($dataArray[$childIdentifier]);
				}

				$nestedDataArray[$parentIdentifier] = $nestedElements;
			}

			// Apply structures to instance data array:
			$this->dataArray = array();
			foreach ($dataArray as $dataElementIdentifier => $dataElement) {
				$this->dataArray[] = $dataElement;

				if (!empty($nestedDataArray[$dataElementIdentifier])) {
					$this->dataArray = array_merge(
						$this->dataArray,
						$nestedDataArray[$dataElementIdentifier]
					);
				}
			}
		}
	}

	/**
	 * @return array
	 */
	protected function getDataArrayWithIdentifier() {
		$dataArray = array();

		foreach ($this->dataArray as $dataElement) {
			$dataIdentifier = $dataElement['table'] . ':' . $dataElement['uid'];
			$dataArray[$dataIdentifier] = $dataElement;
		}

		return $dataArray;
	}

	/**
	 * @return array
	 */
	protected function extendDataArray() {
		foreach ($this->dataArray as &$dataElement) {
			$this->setCollectionIdentifier($dataElement);
		}
	}

	/**
	 * @param array $element
	 * @param integer $value
	 */
	protected function setCollectionIdentifier(array &$element, $value = 0) {
		$element[self::GridColumn_Collection] = $value;
	}

	/**
	 * @param integer $index
	 * @return boolean
	 */
	protected function hasDependentCollectionIdentifier($index) {
		return (
			isset($this->dataArray[$index][self::GridColumn_Collection])
			&& $this->dataArray[$index][self::GridColumn_Collection] > 0
		);
	}


	/**
	 * DEPENDENCY HANDLING AND CALLBACKS ======================================
	 */


	/**
	 * @return t3lib_utility_Dependency
	 */
	protected function getDependencyUtility() {
		/** @var $dependency t3lib_utility_Dependency */
		$dependency = t3lib_div::makeInstance('t3lib_utility_Dependency');
		$dependency->setOuterMostParentsRequireReferences(TRUE);

		$dependency->setEventCallback(
			t3lib_utility_Dependency_Element::EVENT_CreateChildReference,
			$this->getDependencyCallback('createNewDependentElementChildReferenceCallback')
		);

		$dependency->setEventCallback(
			t3lib_utility_Dependency_Element::EVENT_CreateParentReference,
			$this->getDependencyCallback('createNewDependentElementParentReferenceCallback')
		);

		return $dependency;
	}

	/**
	 * Gets a new callback to be used in the dependency resolver utility.
	 *
	 * @param string $method
	 * @param array $targetArguments
	 * @return t3lib_utility_Dependency_Callback
	 */
	protected function getDependencyCallback($method, array $targetArguments = array()) {
		return t3lib_div::makeInstance('t3lib_utility_Dependency_Callback', $this, $method, $targetArguments);
	}

	/**
	 * @return t3lib_TCEmain
	 */
	protected function getTceMainHelper() {
		if (!isset($this->tceMainHelper)) {
			$this->tceMainHelper = t3lib_div::makeInstance('t3lib_TCEmain');
		}
		return $this->tceMainHelper;
	}

	/**
	 * Callback to determine whether a new child reference shall be considered in the dependency resolver utility.
	 *
	 * @param array $callerArguments
	 * @param array $targetArgument
	 * @param t3lib_utility_Dependency_Element $caller
	 * @param string $eventName
	 * @return string Skip response (if required)
	 */
	public function createNewDependentElementChildReferenceCallback(array $callerArguments, array $targetArgument, t3lib_utility_Dependency_Element $caller, $eventName) {
		/** @var $reference t3lib_utility_Dependency_Reference */
		$reference = $callerArguments['reference'];

		$fieldCOnfiguration = t3lib_BEfunc::getTcaFieldConfiguration($caller->getTable(), $reference->getField());

		if (!$fieldCOnfiguration || !t3lib_div::inList('field,list', $this->getTceMainHelper()->getInlineFieldType($fieldCOnfiguration))) {
			return t3lib_utility_Dependency_Element::RESPONSE_Skip;
		}
	}

	/**
	 * Callback to determine whether a new parent reference shall be considered in the dependency resolver utility.
	 *
	 * @param array $callerArguments
	 * @param array $targetArgument
	 * @param t3lib_utility_Dependency_Element $caller
	 * @param string $eventName
	 * @return string Skip response (if required)
	 */
	public function createNewDependentElementParentReferenceCallback(array $callerArguments, array $targetArgument, t3lib_utility_Dependency_Element $caller, $eventName) {
		/** @var $reference t3lib_utility_Dependency_Reference */
		$reference = $callerArguments['reference'];

		$fieldCOnfiguration = t3lib_BEfunc::getTcaFieldConfiguration($reference->getElement()->getTable(), $reference->getField());

		if (!$fieldCOnfiguration || !t3lib_div::inList('field,list', $this->getTceMainHelper()->getInlineFieldType($fieldCOnfiguration))) {
			return t3lib_utility_Dependency_Element::RESPONSE_Skip;
		}
	}
}

?>