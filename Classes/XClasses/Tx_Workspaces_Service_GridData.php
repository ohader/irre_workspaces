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
		$dependency = $this->getDependencyService()->create();

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
				$collectionIdentifier++;
				$nestedElements = array();
				$parentIdentifier = $outerMostParent->__toString();

				if (isset($dataArray[$parentIdentifier])) {
					$this->setCollectionIdentifier(
						$dataArray[$parentIdentifier],
						$collectionIdentifier
					);
				}

				/** @var $child t3lib_utility_Dependency_Element */
				foreach ($outerMostParent->getNestedChildren() as $child) {
					$childIdentifier = $child->__toString();

					if (isset($dataArray[$childIdentifier])) {
						$this->setCollectionIdentifier(
							$dataArray[$childIdentifier],
							$collectionIdentifier
						);

						$nestedElements[] = $dataArray[$childIdentifier];
						unset($dataArray[$childIdentifier]);
					}
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
	 * @return Tx_IrreWorkspaces_Service_DependencyService
	 */
	protected function getDependencyService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_DependencyService');
	}
}

?>