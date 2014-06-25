<?php
namespace OliverHader\IrreWorkspaces\Service\Action;

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
use TYPO3\CMS\Version\Dependency\ReferenceEntity;

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
abstract class AbstractService implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * @var \TYPO3\CMS\Workspaces\Service\StagesService
	 */
	protected $workspacesStagesService;

	/**
	 * @var array|\OliverHader\IrreWorkspaces\Domain\Model\Dependency\IncompleteStructure[]
	 */
	protected $incompleteStructures = array();

	/**
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected $dataHandler;

	/**
	 * @param ElementEntity $outerMostParent
	 * @param array|ElementEntity[] $intersectingElements
	 * @param array|ElementEntity[] $differentElements
	 * @return \OliverHader\IrreWorkspaces\Domain\Model\Dependency\IncompleteStructure
	 */
	public function addIncompleteStructure(ElementEntity $outerMostParent, array $intersectingElements, array $differentElements) {
		$incompleteStructure = \OliverHader\IrreWorkspaces\Domain\Model\Dependency\IncompleteStructure::create(
			$outerMostParent,
			$intersectingElements,
			$differentElements
		);
		$this->incompleteStructures[] = $incompleteStructure;
		return $incompleteStructure;
	}

	/**
	 * @param ElementEntity $element
	 * @param string $comment
	 * @return NULL|integer
	 */
	protected function cloneLiveVersion(ElementEntity $element, $comment) {
		$clonedId = $this->getClonedId($element);

		// See, whether a clone has been created already
		// e.g. during cloning a parent thus all children
		if (empty($clonedId)) {
			$versionRecord = \TYPO3\CMS\Backend\Utility\BackendUtility::getWorkspaceVersionOfRecord(
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
	 * @param ElementEntity $element
	 * @return NULL|integer
	 */
	protected function getLiveId(ElementEntity $element) {
		$liveId = $element->getDataValue('liveId');

		if (empty($liveId)) {
			$liveId = \TYPO3\CMS\Backend\Utility\BackendUtility::getLiveVersionIdOfRecord($element->getTable(), $element->getId());
		}

		return $liveId;
	}

	/**
	 * @param ElementEntity $element
	 * @return NULL|integer
	 */
	protected function getClonedId(ElementEntity $element) {
		$clonedId = $element->getDataValue('clonedId');
		return $clonedId;
	}

	/**
	 * @param ElementEntity $element
	 * @return NULL|integer
	 */
	protected function getFallbackId(ElementEntity $element) {
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
	 * @param array|ReferenceEntity[] $childReferences
	 * @return array|ElementEntity[]
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
	 * @return \TYPO3\CMS\Core\Database\RelationHandler
	 */
	public function getReferenceCollection($parentTable, $parentId, array $parentConfiguration) {
		/** @var $referenceCollection \TYPO3\CMS\Core\Database\RelationHandler */
		$referenceCollection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_loadDBGroup');
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
	 * @param array|ElementEntity[] $elements
	 * @param string $table
	 * @param integer $id
	 * @param string $dataValueKey
	 * @return NULL|ElementEntity
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
	 * @return \TYPO3\CMS\Workspaces\Service\StagesService
	 */
	protected function getWorkspacesStagesService() {
		if (!isset($this->workspacesStagesService)) {
			$this->workspacesStagesService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Workspaces\\Service\\StagesService'
			);
		}
		return $this->workspacesStagesService;
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguage() {
		return $GLOBALS['LANG'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}
}

?>