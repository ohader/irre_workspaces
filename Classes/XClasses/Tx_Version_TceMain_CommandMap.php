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
class ux_tx_version_tcemain_CommandMap extends tx_version_tcemain_CommandMap {
	/**
	 * Applies the workspaces dependencies and removes incomplete structures or automatically
	 * completes them, depending on the options.workspaces.considerReferences setting
	 *
	 * @param t3lib_utility_Dependency $dependency
	 * @param string $scope
	 * @return void
	 */
	protected function applyWorkspacesDependencies(t3lib_utility_Dependency $dependency, $scope) {
		$configurationService = Tx_IrreWorkspaces_Service_ConfigurationService::getInstance();

		if ($scope === self::SCOPE_WorkspacesSwap && $configurationService->getEnableRecordSinglePublish()) {
			$this->applyWorkspacesSwapDependencies($dependency, $scope);
		} elseif ($scope === self::SCOPE_WorkspacesClear && $configurationService->getEnableRecordSingleFlush()) {
			$this->applyWorkspacesClearDependencies($dependency, $scope);
		} else {
			parent::applyWorkspacesDependencies($dependency, $scope);
		}
	}

	/**
	 * @param t3lib_utility_Dependency $dependency
	 * @param string $scope
	 */
	protected function applyWorkspacesSwapDependencies(t3lib_utility_Dependency $dependency, $scope) {
		$incompleteStructures = $this->applyWorkspacesDependenciesWithIncompleteStrategy(
			$dependency,
			$scope,
			$this->getPublishWorkspaceActionService()
		);
	}

	/**
	 * @param t3lib_utility_Dependency $dependency
	 * @param string $scope
	 */
	protected function applyWorkspacesClearDependencies(t3lib_utility_Dependency $dependency, $scope) {
		$incompleteStructures = $this->applyWorkspacesDependenciesWithIncompleteStrategy(
			$dependency,
			$scope,
			$this->getFlushWorkspaceActionService()
		);

		foreach ($incompleteStructures as $incompleteStructure) {
			$this->fetchParentReferenceCollection(
				$this->getFlushWorkspaceActionService(),
				$incompleteStructure->getOuterMostParent()
			);

			if ($this->workspacesConsiderReferences) {
				$this->applyWorkspacesClearDeletedDependencies(
					$this->getFlushWorkspaceActionService(),
					$incompleteStructure->getOuterMostParent(),
					$incompleteStructure
				);
			}
		}
	}

	/**
	 * @param Tx_IrreWorkspaces_Service_Action_AbstractActionService $handler
	 * @param t3lib_utility_Dependency_Element $parentElement
	 */
	protected function fetchParentReferenceCollection(Tx_IrreWorkspaces_Service_Action_AbstractActionService $handler, t3lib_utility_Dependency_Element $parentElement) {
		$referenceCollections = array();

		if (count($parentElement->getChildren()) > 0) {
			$parentTable = $parentElement->getTable();
			$parentId = $parentElement->getId();
			t3lib_div::loadTCA($parentTable);

			$childrenPerParentField = $handler->getChildrenPerParentField(
				$parentElement->getChildren()
			);

			foreach ($childrenPerParentField as $parentField => $children) {
				foreach ($children as $child) {
					$this->fetchParentReferenceCollection($handler, $child);
				}

				$parentConfiguration = $GLOBALS['TCA'][$parentTable]['columns'][$parentField]['config'];
				$referenceCollection = $handler->getReferenceCollection($parentTable, $parentId, $parentConfiguration);
				$referenceCollections[$parentField] = $referenceCollection;
			}
		}

		$parentElement->setDataValue('referenceCollections', $referenceCollections);
	}

	/**
	 * @param Tx_IrreWorkspaces_Service_Action_AbstractActionService $handler
	 * @param t3lib_utility_Dependency_Element $parentElement
	 * @param Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure $incompleteStructure
	 */
	protected function applyWorkspacesClearDeletedDependencies(Tx_IrreWorkspaces_Service_Action_AbstractActionService $handler, t3lib_utility_Dependency_Element $parentElement, Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure $incompleteStructure) {
		if (count($parentElement->getChildren())) {
			$transformDependentElementsToUseLiveId = $this->getScopeData(
				self::SCOPE_WorkspacesClear,
				self::KEY_TransformDependentElementsToUseLiveId
			);

			$parentRecord = $parentElement->getRecord();
			$parentIdentifier = $parentElement->__toString();
			$missingElements = array();

			// Only if current parent is considered to be cleared and parent uses deleted placeholder
			if ($incompleteStructure->hasIntersectingElement($parentIdentifier) && $parentRecord['t3ver_state'] == 2) {
				// @todo Invoke parents as well

				/** @var $childReference t3lib_utility_Dependency_Reference */
				foreach ($parentElement->getChildren() as $childReference) {
					$childRecord = $childReference->getElement()->getRecord();
					$childIdentifier = $childReference->getElement()->__toString();

					if ($incompleteStructure->hasDifferentElement($childIdentifier) && $childRecord['t3ver_state'] == 2) {
						$missingElements[$childIdentifier] = $childReference->getElement();
					}

					$this->applyWorkspacesClearDeletedDependencies(
						$handler,
						$childReference->getElement(),
						$incompleteStructure
					);
				}

				// Add to command map
				if (count($missingElements)) {
					if ($transformDependentElementsToUseLiveId) {
						$missingElements = $this->transformDependentElementsToUseLiveId($missingElements);
					}

					$this->update($parentElement, $missingElements, self::SCOPE_WorkspacesClear);

					// Update information in incomplete structure
					$intersectingElements = array_merge(
						$incompleteStructure->getIntersectingElements(),
						$missingElements
					);
					$differentElements = array_diff_key(
						$incompleteStructure->getDifferentElements(),
						$missingElements
					);

					$incompleteStructure->setIntersectingElements($intersectingElements);
					$incompleteStructure->setDifferentElements($differentElements);
				}
			}
		}
	}

	/**
	 * @param t3lib_utility_Dependency $dependency
	 * @param string $scope
	 * @param Tx_IrreWorkspaces_Service_Action_AbstractActionService $handler
	 * @return array|Tx_IrreWorkspaces_Domain_Model_Dependency_IncompleteStructure[]
	 */
	protected function applyWorkspacesDependenciesWithIncompleteStrategy(t3lib_utility_Dependency $dependency, $scope, Tx_IrreWorkspaces_Service_Action_AbstractActionService $handler) {
		$incompleteStructures = array();

		$transformDependentElementsToUseLiveId = $this->getScopeData($scope, self::KEY_TransformDependentElementsToUseLiveId);

		$elementsToBeVersionized = $dependency->getElements();

			// Use the uid of the live record instead of the workspace record:
		if ($transformDependentElementsToUseLiveId) {
			$elementsToBeVersionized = $this->transformDependentElementsToUseLiveId($elementsToBeVersionized);
		}

		$outerMostParents = $dependency->getOuterMostParents();
		/** @var $outerMostParent t3lib_utility_Dependency_Element */
		foreach ($outerMostParents as $outerMostParent) {
			$dependentElements = $dependency->getNestedElements($outerMostParent);
			if ($transformDependentElementsToUseLiveId) {
				$dependentElements = $this->transformDependentElementsToUseLiveId($dependentElements);
			}

			// Gets the difference (intersection) between elements that were submitted by the user
			// and the evaluation of all dependent records that should be used for this action instead:
			$intersectingElements = array_intersect_key($dependentElements, $elementsToBeVersionized);
			$differentElements = array_diff_key($dependentElements, $elementsToBeVersionized);

			if (count($intersectingElements) > 0) {
				// Use all intersecting elements (even if the structure is not complete)
				$this->update(current($intersectingElements), $intersectingElements, $scope);

				// If at least one element intersects but not all, remember for later processing
				if (count($intersectingElements) !== count($dependentElements)) {
					$incompleteStructures[] = $handler->addIncompleteStructure(
						$outerMostParent,
						$intersectingElements,
						$differentElements
					);
				}
			}
		}

		return $incompleteStructures;
	}

	/**
	 * Constructs the scope settings.
	 * Currently the scopes for swapping/publishing and staging are available.
	 *
	 * @return void
	 */
	protected function constructScopes() {
		parent::constructScopes();

		if (Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableRecordSingleFlush()) {
			$this->scopes[self::SCOPE_WorkspacesClear][self::KEY_ElementCreateChildReferenceCallback] = 'createNewDependentElementChildReferenceCallback';
			$this->scopes[self::SCOPE_WorkspacesClear][self::KEY_ElementCreateParentReferenceCallback] = 'createNewDependentElementParentReferenceCallback';
		}
	}


	/**
	 * @return Tx_IrreWorkspaces_Service_Action_FlushWorkspaceActionService
	 */
	protected function getFlushWorkspaceActionService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Action_FlushWorkspaceActionService');
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService
	 */
	protected function getPublishWorkspaceActionService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService');
	}
}

?>