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
abstract class Tx_IrreWorkspaces_Service_Action_AbstractActionService implements t3lib_Singleton {
	/**
	 * @var Tx_Workspaces_Service_Stages
	 */
	protected $workspacesStagesService;

	/**
	 * @var array|Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure[]
	 */
	protected $incompleteStructures = array();

	/**
	 * @var t3lib_TCEmain
	 */
	protected $dataHandler;

	/**
	 * @param t3lib_utility_Dependency_Element $outerMostParent
	 * @param array|t3lib_utility_Dependency_Element[] $intersectingElements
	 * @param array|t3lib_utility_Dependency_Element[] $differentElements
	 * @return Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure
	 */
	public function addIncompleteStructure(t3lib_utility_Dependency_Element $outerMostParent, array $intersectingElements, array $differentElements) {
		$incompleteStructure = Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure::create(
			$outerMostParent,
			$intersectingElements,
			$differentElements
		);
		$this->incompleteStructures[] = $incompleteStructure;
		return $incompleteStructure;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @param string $comment
	 * @return NULL|integer
	 */
	protected function cloneLiveVersion(t3lib_utility_Dependency_Element $element, $comment) {
		$clonedId = $this->getClonedId($element);

		// See, whether a clone has been created already
		// e.g. during cloning a parent thus all children
		if (empty($clonedId)) {
			$versionRecord = t3lib_BEfunc::getWorkspaceVersionOfRecord(
				$this->getBackendUser()->workspace,
				$element->getTable(),
				$this->getLiveId($element),
				'uid'
			);

			if (!empty($versionRecord['uid'])) {
				$clonedId = $versionRecord['uid'];
				$element->setDataValue('clonedId', $clonedId);
			}
		}

		// If there's nothing, clone the live version
		if (empty($clonedId)) {
			$clonedId = $this->dataHandler->versionizeRecord(
				$element->getTable(),
				$this->getLiveId($element),
				$comment
			);

			$element->setDataValue('clonedId', $clonedId);
		}

		return $clonedId;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return NULL|integer
	 */
	protected function getLiveId(t3lib_utility_Dependency_Element $element) {
		$liveId = $element->getDataValue('liveId');

		if (empty($liveId)) {
			$liveId = t3lib_BEfunc::getLiveVersionIdOfRecord($element->getTable(), $element->getId());
		}

		return $liveId;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return NULL|integer
	 */
	protected function getClonedId(t3lib_utility_Dependency_Element $element) {
		$clonedId = $element->getDataValue('clonedId');
		return $clonedId;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return NULL|integer
	 */
	protected function getFallbackId(t3lib_utility_Dependency_Element $element) {
		$id = $this->getClonedId($element);

		if (empty($id)) {
			$id = $element->getId();
		}

		return $id;
	}

	/**
	 * @param string $table
	 * @param integer $id
	 * @param string $field
	 * @return NULL|integer
	 */
	protected function findRemapStackIndex($table, $id, $field) {
		$remapStackIndex = NULL;

		if (!empty($this->dataHandler->remapStackRecords[$table])) {
			foreach ($this->dataHandler->remapStackRecords[$table] as $uniqeId => $data) {
				$index = $data['remapStackIndex'];

				if ((int) $this->dataHandler->substNEWwithIDs[$uniqeId] === (int) $id) {
					if ($this->dataHandler->remapStack[$index]['field'] === $field) {
						$remapStackIndex = $index;
						break;
					}
				}
			}
		}

		return $remapStackIndex;
	}

	/**
	 * @param integer $remapStackIndex
	 * @param string $key
	 * @param mixed $value
	 */
	protected function updateRemapStack($remapStackIndex, $key, $value) {
		if (isset($this->dataHandler->remapStack[$remapStackIndex]['pos'][$key])) {
			$position = $this->dataHandler->remapStack[$remapStackIndex]['pos'][$key];
			$this->dataHandler->remapStack[$remapStackIndex]['args'][$position] = $value;
		}
	}

	/**
	 * @param array|t3lib_utility_Dependency_Reference[] $childReferences
	 * @return array|t3lib_utility_Dependency_Element[]
	 */
	public function getChildrenPerParentField(array $childReferences) {
		$childrenPerParentField = array();

		foreach ($childReferences as $childReference) {
			$childrenPerParentField[$childReference->getField()][] = $childReference->getElement();
		}

		return $childrenPerParentField;
	}

	/**
	 * @param string $parentTable
	 * @param integer $parentId
	 * @param array $parentConfiguration
	 * @return t3lib_loadDBGroup
	 */
	public function getReferenceCollection($parentTable, $parentId, array $parentConfiguration) {
		/** @var $referenceCollection t3lib_loadDBGroup */
		$referenceCollection = t3lib_div::makeInstance('t3lib_loadDBGroup');
		$referenceCollection->start(
			'',
			$parentConfiguration['foreign_table'],
			$parentConfiguration['MM'],
			$parentId,
			$parentTable,
			$parentConfiguration
		);
		return $referenceCollection;
	}

	/**
	 * @param array|t3lib_utility_Dependency_Element[] $elements
	 * @param string $table
	 * @param integer $id
	 * @return NULL|t3lib_utility_Dependency_Element
	 */
	public function findElement(array $elements, $table, $id, $dataValueKey = NULL) {
		$result = NULL;

		foreach ($elements as $element) {
			$elementId = $element->getId();

			if ($dataValueKey !== NULL) {
				$elementId = $element->getDataValue($dataValueKey);
			}

			if ($element->getTable() === $table && $elementId == $id) {
				$result = $element;
				break;
			}
		}

		return $result;
	}

	/**
	 * @param string $table
	 * @param integer $id
	 * @param array $values
	 */
	protected function updateRecord($table, $id, array $values) {
		$this->getDatabase()->exec_UPDATEquery($table, 'uid=' . (int) $id, $values);
	}

	/**
	 * @return Tx_Workspaces_Service_Stages
	 */
	protected function getWorkspacesStagesService() {
		if (!isset($this->workspacesStagesService)) {
			$this->workspacesStagesService = t3lib_div::makeInstance('Tx_Workspaces_Service_Stages');
		}
		return $this->workspacesStagesService;
	}

	/**
	 * @return language
	 */
	protected function getLanguage() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return t3lib_beUserAuth
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}
}

?>