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
		if ($scope === self::SCOPE_WorkspacesSwap) {
			$this->applyWorkspacesSwapDependencies($dependency, $scope);
		} else {
			parent::applyWorkspacesDependencies($dependency, $scope);
		}
	}

	protected function applyWorkspacesSwapDependencies(t3lib_utility_Dependency $dependency, $scope) {
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
					$this->getPublishWorkspaceActionService()->addIncompleteStructure(
						$outerMostParent,
						$intersectingElements,
						$differentElements
					);
				}
			}
		}
	}

	protected function applyWorkspacesClearDependencies(t3lib_utility_Dependency $dependency, $scope) {

	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService
	 */
	protected function getPublishWorkspaceActionService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService');
	}
}

?>