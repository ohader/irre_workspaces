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
	const GridColumn_Title = 'Tx_IrreWorkspaces_Title';
	const GridColumn_Collection = 'Tx_IrreWorkspaces_Collection';
	const GridColumn_Modification = 'Tx_IrreWorkspaces_Modification';

	/**
	 * @var t3lib_TCEmain
	 */
	protected $tceMainHelper;

	/**
	 * @var Tx_IrreWorkspaces_Service_ComparisonService
	 */
	protected $comparisonService;

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
		$this->reduceDataArray();
		$this->purgeDataArray();
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

		$lastIndex = $start + count($dataArrayPart) - 1;
		$collectionIdentifier = $this->getDependentCollectionIdentifier($lastIndex);
		$dataCount = count($this->dataArray);

		if ($collectionIdentifier !== NULL) {
			for ($i = $lastIndex + 1; $i < $dataCount && $collectionIdentifier === $this->getDependentCollectionIdentifier($i); $i++) {
				$dataArrayPart[] = $this->dataArray[$i];
			}
		}

		return $dataArrayPart;
	}

	/**
	 * Checks if a cache entry is given for given versions and filter text and tries to load the data array from cache.
	 *
	 * @param array $versions All records uids etc. First key is table name, second key incremental integer. Records are associative arrays with uid, t3ver_oid and t3ver_swapmode fields. The pid of the online record is found as "livepid" the pid of the offline record is found in "wspid"
	 * @param string $filterTxt The given filter text from the grid.
	 * @return boolean
	 */
	protected function getDataArrayFromCache (array $versions, $filterTxt) {
		$result = FALSE;

		if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableCache()) {
			$result = parent::getDataArrayFromCache($versions, $filterTxt);
		}

		return $result;
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
				$nestedElements = array();
				$collectionIdentifier++;

				$parentIdentifier = $outerMostParent->__toString();
				$parentTitle = t3lib_BEfunc::getRecordTitle(
					$outerMostParent->getTable(),
					$outerMostParent->getRecord()
				);

				$isParentOnWorkspace = isset($dataArray[$parentIdentifier]);

				/** @var $child t3lib_utility_Dependency_Element */
				foreach ($outerMostParent->getNestedChildren() as $child) {
					$childIdentifier = $child->__toString();

					if (isset($dataArray[$childIdentifier])) {
						$this->setElementProperties(
							$dataArray[$childIdentifier],
							array(
								self::GridColumn_Title => $parentTitle,
								self::GridColumn_Collection => $collectionIdentifier,
								self::GridColumn_Modification => $this->isElementModified($child),
							)
						);

						if ($isParentOnWorkspace) {
							$nestedElements[] = $dataArray[$childIdentifier];
							unset($dataArray[$childIdentifier]);
						}
					}
				}

				if ($isParentOnWorkspace) {
					$this->setElementProperties(
						$dataArray[$parentIdentifier],
						array(
							self::GridColumn_Title => $parentTitle,
							self::GridColumn_Collection => $collectionIdentifier,
							self::GridColumn_Modification => $this->isElementModified($outerMostParent),
						)
					);

					$nestedDataArray[$parentIdentifier] = $nestedElements;
				}
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
	 * @return void
	 */
	protected function reduceDataArray() {
		if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableRecordReduction()) {
			foreach ($this->dataArray as $index => $dataElement) {
				$combinedRecord = Tx_Workspaces_Domain_Model_CombinedRecord::create(
					$dataElement['table'],
					$dataElement['t3ver_oid'],
					$dataElement['uid']
				);

				if ($this->getDeviationService()->hasDeviation($combinedRecord) === FALSE) {
					unset($this->dataArray[$index]);
				}
			}

			// Update array index
			$this->dataArray = array_merge($this->dataArray, array());
		}
	}

	/**
	 * @return void
	 */
	protected function extendDataArray() {
		foreach ($this->dataArray as &$dataElement) {
			$dataElement = $this->setCollectionIdentifier($dataElement);
		}
	}

	/**
	 * @return void
	 */
	protected function purgeDataArray() {
		foreach ($this->dataArray as $key => $dataElement) {
			if (isset($dataElement[self::GridColumn_Modification]) && $dataElement[self::GridColumn_Modification] === FALSE) {
				unset($this->dataArray[$key]);
			}
		}

		// Update array index
		$this->dataArray = array_merge($this->dataArray, array());
	}

	/**
	 * @param array $element
	 * @param integer $value
	 * @return array
	 */
	protected function setCollectionIdentifier(array $element, $value = 0) {
		$element[self::GridColumn_Collection] = $value;
		return $element;
	}

	/**
	 * @param array $element
	 * @param array $properties
	 */
	protected function setElementProperties(array &$element, array $properties) {
		foreach ($properties as $identifier => $value) {
			$element[$identifier] = $value;
		}
	}

	/**
	 * @param integer $index
	 * @return boolean
	 */
	protected function hasDependentCollectionIdentifier($index) {
		return !empty($this->dataArray[$index][self::GridColumn_Collection]);
	}

	/**
	 * @param integer $index
	 * @return integer|NULL
	 */
	protected function getDependentCollectionIdentifier($index) {
		$result = NULL;

		if (!empty($this->dataArray[$index][self::GridColumn_Collection])) {
			$result = $this->dataArray[$index][self::GridColumn_Collection];
		}

		return $result;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return boolean
	 */
	protected function isElementModified(t3lib_utility_Dependency_Element $element) {
		$element->setDataValue(
			Tx_IrreWorkspaces_Service_ComparisonService::KEY_Origin,
			t3lib_BEfunc::getLiveVersionOfRecord($element->getTable(),$element->getId())
		);

		return $this->getComparisonService()->hasDifferences($element);
	}

	/**
	 * @param array $callerArguments
	 * @param array $targetArguments
	 * @param Tx_IrreWorkspaces_Service_ComparisonService $caller
	 * @param string $eventName
	 */
	public function comparisonServicePostProcessDifferencesCallback(array $callerArguments, array $targetArguments, Tx_IrreWorkspaces_Service_ComparisonService $caller, $eventName) {
		$element = $callerArguments['element'];
		$differences =& $callerArguments['differences'];

		foreach ($differences as $field => $value) {
			$fieldConfiguration = $caller->getTcaConfiguration($element, $field);

			if (preg_match('#^(uid|pid|tstamp|crdate|t3_origuid|t3ver_.+)$#', $field)) {
				unset($differences[$field]);
			} elseif (t3lib_div::inList('passthrough,none', $fieldConfiguration['type'])) {
				unset($differences[$field]);
			}
		}
	}

	/**
	 * Calculates the percentage of changes between two records.
	 *
	 * @param string $table
	 * @param array $diffRecordOne
	 * @param array $diffRecordTwo
	 * @return integer
	 * @scope performance
	 */
	public function calculateChangePercentage($table, array $diffRecordOne, array $diffRecordTwo) {
		return 0;
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Record_DeviationService
	 */
	protected function getDeviationService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Record_DeviationService');
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_DependencyService
	 */
	protected function getDependencyService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_DependencyService');
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_ComparisonService
	 */
	protected function getComparisonService() {
		if (!isset($this->comparisonService)) {
			$this->comparisonService = t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_ComparisonService');

			$this->comparisonService->setEventCallback(
				Tx_IrreWorkspaces_Service_ComparisonService::EVENT_PostProcessDifferences,
				$this->createEventCallback('comparisonServicePostProcessDifferencesCallback')
			);
		}

		return $this->comparisonService;
	}

	/**
	 * Gets a new callback to be used in the dependency resolver utility.
	 *
	 * @param string $method
	 * @param array $targetArguments
	 * @return t3lib_utility_Dependency_Callback
	 */
	protected function createEventCallback($method, array $targetArguments = array()) {
		return t3lib_div::makeInstance('t3lib_utility_Dependency_Callback', $this, $method, $targetArguments);
	}
}

?>