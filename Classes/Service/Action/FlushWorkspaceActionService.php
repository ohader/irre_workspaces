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
	 * @param t3lib_TCEmain $dataHandler
	 */
	public function finish(t3lib_TCEmain $dataHandler) {
		if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableRecordSingleFlush()) {
			$this->dataHandler = $dataHandler;

			foreach ($this->incompleteStructures as $incompleteStructure) {
				$this->processParent($incompleteStructure->getOuterMostParent(), $incompleteStructure, $dataHandler);
			}
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $parentElement
	 * @param Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure $incompleteStructure
	 */
	protected function processParent(t3lib_utility_Dependency_Element $parentElement, Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure $incompleteStructure) {
		$clonedParentId = NULL;
		$flushedChildren = array();
		$unflushedChildren = array();

		$isElementFlushed = $incompleteStructure->hasIntersectingElement($parentElement->__toString());

		/** @var $childReference t3lib_utility_Dependency_Reference */
		foreach ($parentElement->getChildren() as $childReference) {
			$this->processParent($childReference->getElement(), $incompleteStructure);

			$isChildFlushed = $incompleteStructure->hasIntersectingElement($childReference->getElement()->__toString());
			$childRecord = $childReference->getElement()->getRecord();

			if ($isChildFlushed) {
				// Only if child is modified (and thus has pendant in live version)
				if ($childRecord['t3ver_state'] == 0) {
					$flushedChildren[] = $childReference;
				} else {
					$childReference->getElement()->setDataValue('skipReference', TRUE);
				}
			} else {
				$unflushedChildren[] = $childReference;
			}
		}

		if ($isElementFlushed === TRUE && count($unflushedChildren) > 0) {
			// Clone flushed modified children
			foreach ($flushedChildren as $childReference) {
				$this->cloneLiveVersion($childReference->getElement(), 'Auto-created for WS #' . $this->getBackendUser()->workspace);
			}

			$this->cloneLiveVersion($parentElement, 'Auto-created for WS #' . $this->getBackendUser()->workspace);

			$this->updateVersionReferences($parentElement, $flushedChildren, $unflushedChildren);

			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$parentElement->getId()
			);
			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$this->getFallbackId($parentElement)
			);
		}

		if ($isElementFlushed === FALSE && count($flushedChildren) > 0) {
			// Clone published children, otherwise the not published parent would be incomplete
			foreach ($flushedChildren as $childReference) {
				$this->cloneLiveVersion($childReference->getElement(), 'Auto-created for WS #' . $this->getBackendUser()->workspace);
			}

			$this->updateVersionReferences($parentElement, $flushedChildren, $unflushedChildren);

			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$parentElement->getId()
			);
			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$this->getFallbackId($parentElement)
			);
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $parentElement
	 * @param array $flushedChildren
	 * @param array $unflushedChildren
	 */
	protected function updateVersionReferences(t3lib_utility_Dependency_Element $parentElement, array $flushedChildren, array $unflushedChildren) {
		$childrenPerParentField = $this->getChildrenPerParentField($parentElement->getChildren());

		$parentId = $this->getFallbackId($parentElement);
		$parentTable = $parentElement->getTable();
		t3lib_div::loadTCA($parentTable);

		/** @var $children t3lib_utility_Dependency_Element[] */
		foreach ($childrenPerParentField as $parentField => $children) {
			$parentConfiguration = $GLOBALS['TCA'][$parentTable]['columns'][$parentField]['config'];
			$referenceCollection = $this->getReferenceCollection($parentTable, $parentId, $parentConfiguration);
			$referenceCollection->itemArray = array();
			$referenceCollection->tableArray = array();

			// If there are unflushed children, use previous version reference collection
			if (count($unflushedChildren) > 0) {
				$childDataValueKey = NULL;
				$parentReferenceCollections = $parentElement->getDataValue('referenceCollections');
				/** @var $parentReferenceCollection t3lib_loadDBGroup */
				$parentReferenceCollection = $parentReferenceCollections[$parentField];

			// Otherwise (there are no unflushed children), use the live reference collection
			} else {
				$childDataValueKey = 'liveId';
				$parentReferenceCollection = $this->getReferenceCollection(
					$parentTable,
					$this->getLiveId($parentElement),
					$parentConfiguration
				);
			}

			foreach ($parentReferenceCollection->itemArray as $item) {
				$childElement = $this->findElement($children, $item['table'], $item['id'], $childDataValueKey);

				// Child element is found in current processed collection
				if ($childElement !== NULL && $childElement->getDataValue('skipReference') !== TRUE) {
					$referenceCollection->itemArray[] = array(
						'table' => $childElement->getTable(),
						'id' => $this->getFallbackId($childElement),
					);
					$referenceCollection->tableArray[$childElement->getTable()][] = $this->getFallbackId($childElement);

				// If child element was not found, guess(!) the version id and add the element
				} else {
					$possibleVersionId = t3lib_BEfunc::wsMapId($item['table'], $item['id']);
					$referenceCollection->itemArray[] = array(
						'table' => $item['table'],
						'id' => $possibleVersionId,
					);
					$referenceCollection->tableArray[$item['table']][] = $possibleVersionId;
				}
			}

			// Persist changes
			if (NULL !== $remapStackIndex = $this->findRemapStackIndex($parentTable, $parentId, $parentField)) {
				$this->updateRemapStack($remapStackIndex, 'valueArray', $referenceCollection->getValueArray());
			} else {
				$referenceCollection->writeForeignField($parentConfiguration, $parentId);
				$this->updateRecord($parentTable, $parentId, array($parentField => count($referenceCollection->itemArray)));
			}
		}
	}
}

?>