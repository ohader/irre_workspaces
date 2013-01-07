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
class Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService extends Tx_IrreWorkspaces_Service_Action_AbstractActionService {
	/**
	 * @var t3lib_TCEmain
	 */
	protected $dataHandler;

	/**
	 * @var array|Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure[]
	 */
	protected $incompleteStructures = array();

	/**
	 * @param t3lib_utility_Dependency_Element $outerMostParent
	 * @param array|t3lib_utility_Dependency_Element[] $intersectingElements
	 * @param array|t3lib_utility_Dependency_Element[] $differentElements
	 */
	public function addIncompleteStructure(t3lib_utility_Dependency_Element $outerMostParent, array $intersectingElements, array $differentElements) {
		$this->incompleteStructures[] = Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure::create(
			$outerMostParent,
			$intersectingElements,
			$differentElements
		);
	}

	/**
	 * @param t3lib_TCEmain $dataHandler
	 */
	public function finish(t3lib_TCEmain $dataHandler) {
		if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableRecordSinglePublish()) {
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
		$publishedChildren = array();
		$unpublishedChildren = array();

		$isElementPublished = $incompleteStructure->hasIntersectingElement($parentElement->__toString());

		/** @var $childReference t3lib_utility_Dependency_Reference */
		foreach ($parentElement->getChildren() as $childReference) {
			$this->processParent($childReference->getElement(), $incompleteStructure);

			$isChildPublished = $incompleteStructure->hasIntersectingElement($childReference->getElement()->__toString());

			if ($isChildPublished) {
				$publishedChildren[] = $childReference;
			} else {
				$unpublishedChildren[] = $childReference;
			}
		}

		if ($isElementPublished === TRUE && count($unpublishedChildren) > 0) {
			// Clone published children, otherwise the cloned parent would be incomplete
			foreach ($publishedChildren as $childReference) {
				$this->cloneLiveVersion($childReference->getElement(), 'Auto-created for WS #' . $this->getBackendUser()->workspace);
			}

			$this->cloneLiveVersion($parentElement, 'Auto-created for WS #' . $this->getBackendUser()->workspace);

			// @todo Use $this->dataHandler->remapStackRecords instead (if it exists)
			$this->updateLiveReferences($parentElement, $unpublishedChildren);
			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$this->getLiveId($parentElement)
			);

			$this->updateVersionReferences($parentElement);
			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$this->getFallbackId($parentElement)
			);
		}

		if ($isElementPublished === FALSE && count($publishedChildren) > 0) {
			// Clone published children, otherwise the not published parent would be incomplete
			foreach ($publishedChildren as $childReference) {
				$this->cloneLiveVersion($childReference->getElement(), 'Auto-created for WS #' . $this->getBackendUser()->workspace);
			}

			// @todo Use $this->dataHandler->remapStackRecords instead (if it exists)
			$this->updateLiveReferences($parentElement, $publishedChildren);
			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$this->getLiveId($parentElement)
			);

			$this->updateVersionReferences($parentElement);
			$this->dataHandler->addRemapStackRefIndex(
				$parentElement->getTable(),
				$this->getFallbackId($parentElement)
			);
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $parentElement
	 * @param array|t3lib_utility_Dependency_Reference[] $incompleteChildren
	 */
	protected function updateLiveReferences(t3lib_utility_Dependency_Element $parentElement, array $incompleteChildren) {
		$childrenPerParentField = array();

		$parentId = $this->getLiveId($parentElement);
		$parentTable = $parentElement->getTable();
		t3lib_div::loadTCA($parentTable);

		/** @var $childReference t3lib_utility_Dependency_Reference */
		foreach ($incompleteChildren as $childReference) {
			$childrenPerParentField[$childReference->getField()][] = $childReference->getElement();
		}

		/** @var $children t3lib_utility_Dependency_Element[] */
		foreach ($childrenPerParentField as $parentField => $children) {
			$children = $this->transformElementsToUseLiveId($children);

			$parentConfiguration = $GLOBALS['TCA'][$parentTable]['columns'][$parentField]['config'];
			$referenceCollection = $this->getReferenceCollection($parentTable, $parentId, $parentConfiguration);

			// Substitute children
			foreach ($referenceCollection->itemArray as &$item) {
				$itemIdentifier = $parentElement->getIdentifier($item['table'], $item['id']);

				if (!empty($children[$itemIdentifier])) {
					$item['id'] = $this->getLiveId($children[$itemIdentifier]);
					unset($children[$itemIdentifier]);
				}
			}

			// Add children that could not be substituted
			foreach ($children as $child) {
				$referenceCollection->itemArray[] = array(
					'table' => $child->getTable(),
					'id' => $this->getLiveId($child),
				);
				$referenceCollection->tableArray[$child->getTable()][] = $child->getId();
			}

			$referenceCollection->writeForeignField($parentConfiguration, $parentId);
			$this->updateRecord($parentTable, $parentId, array($parentField => count($referenceCollection->itemArray)));
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $parentElement
	 */
	protected function updateVersionReferences(t3lib_utility_Dependency_Element $parentElement) {
		$childrenPerParentField = array();

		$parentId = $this->getFallbackId($parentElement);
		$parentTable = $parentElement->getTable();
		t3lib_div::loadTCA($parentTable);

		/** @var $childReference t3lib_utility_Dependency_Reference */
		foreach ($parentElement->getChildren() as $childReference) {
			$childrenPerParentField[$childReference->getField()][] = $childReference->getElement();
		}

		/** @var $children t3lib_utility_Dependency_Element[] */
		foreach ($childrenPerParentField as $parentField => $children) {
			$parentConfiguration = $GLOBALS['TCA'][$parentTable]['columns'][$parentField]['config'];
			$referenceCollection = $this->getReferenceCollection($parentTable, $parentId, $parentConfiguration);
			$referenceCollection->itemArray = array();
			$referenceCollection->tableArray = array();

			foreach ($children as $child) {
				$referenceCollection->itemArray[] = array(
					'table' => $child->getTable(),
					'id' => $this->getFallbackId($child),
				);
				$referenceCollection->tableArray[$child->getTable()][] = $this->getFallbackId($child);
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
	 * @param array|t3lib_utility_Dependency_Element[] $elements
	 * @return array|t3lib_utility_Dependency_Element[]
	 */
	protected function transformElementsToUseLiveId(array $elements) {
		$transformedElements = array();

		foreach ($elements as $element) {
			$elementName = t3lib_utility_Dependency_Element::getIdentifier(
				$element->getTable(), $this->getLiveId($element)
			);
			$transformedElements[$elementName] = $element;
		}

		return $transformedElements;
	}
}

?>