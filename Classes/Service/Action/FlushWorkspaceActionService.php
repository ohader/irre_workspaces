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
class Tx_IrreWorkspaces_Service_Action_FlushWorkspaceActionService extends Tx_IrreWorkspaces_Service_Action_AbstractActionService {
	/**
	 * @var array
	 */
	protected $finishParents = array();

	/**
	 * @var array
	 */
	protected $flushedParents = array();

	/**
	 * @var array
	 */
	protected $clonedChildren = array();

	/**
	 * @param string $table
	 * @param integer $versionId
	 * @param boolean $flushElement
	 * @param t3lib_TCEmain $dataHandler
	 * @param ux_tx_version_tcemain $versionDataHandler
	 */
	public function clearElement($table, $versionId, $flushElement, t3lib_TCEmain $dataHandler, ux_tx_version_tcemain $versionDataHandler) {
		/** @var $flushChildren t3lib_utility_Dependency_Element[] */
		$flushChildren = array();
		$hasParents = FALSE;

		$liveRecord = t3lib_BEfunc::getLiveVersionOfRecord($table, $versionId, 'uid,t3ver_state');
		$versionRecord = t3lib_BEfunc::getRecord($table, $versionId);
		$liveId = $liveRecord['uid'];

		// If version record has been modified
		if ($liveRecord && $versionRecord && $versionRecord['t3ver_state'] == 0) {
			$element = $this->getCollectionDependencyService()->getDependency()->addElement($table, $versionId);

			if (count($element->getChildren()) > 0) {
				$this->addFlushedParent($element);
				/** @var $childReference t3lib_utility_Dependency_Reference */
				foreach ($element->getChildren() as $childReference) {
					$flushChildren[] = $childReference->getElement();
				}
			}

			// Re-add clone of live version later on to parent
			if (count($element->getParents()) > 0) {
				$hasParents = TRUE;
				/** @var $parentReference t3lib_utility_Dependency_Reference */
				foreach ($element->getParents() as $parentReference) {
					$this->addFinishParent($parentReference);
				}
			}
		}

		// Clear current record
		$versionDataHandler->invokeParentClearWSID($table, $versionId, $flushElement, $dataHandler);

		// Clear all child elements as well, no matter what state they have
		foreach ($flushChildren as $child) {
			$versionDataHandler->invokeParentClearWSID($child->getTable(), $child->getId(), $flushElement, $dataHandler);
		}

		// We need remapping at the end of all processes
		if ($hasParents) {
			$dataHandler->addRemapAction(
				$table, $versionId,
				array($this, 'cloneClearedChildRemapAction'),
				array($table, $versionId, $liveId, $dataHandler)
			);
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $parentElement
	 */
	protected function addFlushedParent(t3lib_utility_Dependency_Element $parentElement) {
		$identifier = $parentElement->__toString();

		if (!isset($this->flushedParents[$identifier])) {
			$this->flushedParents[$identifier] = $parentElement;
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Reference $parentReference
	 */
	protected function addFinishParent(t3lib_utility_Dependency_Reference $parentReference) {
		$identifier = $parentReference->__toString();

		if (!isset($this->finishParents[$identifier])) {
			$parentTable = $parentReference->getElement()->getTable();
			$parentField = $parentReference->getField();
			$parentId = $parentReference->getElement()->getId();

			// @todo: This won't work on using IRRE in FlexForms - which does not at all in TYPO3 4.5, but in 6.0
			t3lib_div::loadTCA($parentTable);
			$parentConfiguration = $GLOBALS['TCA'][$parentTable]['columns'][$parentField]['config'];

			$this->finishParents[$identifier] = array(
				'parentItems' => $this->getReferenceCollection($parentTable, $parentId, $parentConfiguration)->itemArray,
				'parentReference' => $parentReference,
				'parentConfiguration' => $parentConfiguration,
			);
		}
	}

	/**
	 * Clones child element and adds it to parent structures.
	 *
	 * @param string $childTable
	 * @param integer $childVersionId
	 * @param integer $childLiveId
	 * @param t3lib_TCEmain $dataHandler
	 */
	public function cloneClearedChildRemapAction($childTable, $childVersionId, $childLiveId, t3lib_TCEmain $dataHandler) {
		$childCloneId = $dataHandler->versionizeRecord($childTable, $childLiveId, 'Automatically re-added child');
		$this->clonedChildren[$childTable][$childVersionId] = $childCloneId;
	}

	/**
	 * @param t3lib_TCEmain $dataHandler
	 */
	public function finish(t3lib_TCEmain $dataHandler) {
		/** @var $parentReference t3lib_utility_Dependency_Reference */
		foreach ($this->finishParents as $parent) {
			$parentReference = $parent['parentReference'];
			$parentElementIdentifier = $parentReference->getElement()->__toString();

			// No processing if parent element was flushed
			if (!empty($this->flushedParents[$parentElementIdentifier])) {
				continue;
			}

			$parentItems = $parent['parentItems'];
			$parentConfiguration = $parent['parentConfiguration'];

			$parentTable = $parentReference->getElement()->getTable();
			$parentId = $parentReference->getElement()->getId();

			$this->persistClonedClearedChildReferences($parentTable, $parentId, $parentConfiguration, $parentItems);
		}
	}

	/**
	 * Adds cloned child element to parent structure.
	 *
	 * @param string $parentTable
	 * @param integer $parentId
	 * @param array $parentConfiguration
	 * @param array $parentItems
	 */
	protected function persistClonedClearedChildReferences($parentTable, $parentId, array $parentConfiguration, array $parentItems) {
		$isDirty = FALSE;

		$referenceCollection = $this->getReferenceCollection($parentTable, $parentId, $parentConfiguration);
		$remappedItems = array();
		$remappedTables = array();

		foreach ($parentItems as $parentItem) {
			$childTable = $parentItem['table'];
			$childId = $parentItem['id'];

			if (!empty($referenceCollection->tableArray[$childTable]) && in_array($childId, $referenceCollection->tableArray[$childTable])) {
				$remappedItems[] = $parentItem;
				$remappedTables[$childTable][] = $childId;
			} elseif (!empty($this->clonedChildren[$childTable][$childId])) {
				$isDirty = TRUE;
				$remappedItems[] = array(
					'table' => $childTable,
					'id' => $this->clonedChildren[$childTable][$childId],
				);
				$remappedTables[$childTable][] = $this->clonedChildren[$childTable][$childId];
			} else {
				$isDirty = TRUE;
			}
		}

		if ($isDirty) {
			$referenceCollection->itemArray = $remappedItems;
			$referenceCollection->tableArray = $remappedTables;
			$referenceCollection->writeForeignField($parentConfiguration, $parentId);
		}
	}

	/**
	 * @param string $parentTable
	 * @param integer $parentId
	 * @param array $parentConfiguration
	 * @return t3lib_loadDBGroup
	 */
	protected function getReferenceCollection($parentTable, $parentId, array $parentConfiguration) {
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
	 * @return Tx_IrreWorkspaces_Service_Dependency_CollectionDependencyService
	 */
	protected function getCollectionDependencyService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Dependency_CollectionDependencyService');
	}
}

?>