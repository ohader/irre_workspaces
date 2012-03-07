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
class Tx_IrreWorkspaces_Service_TceMainService implements t3lib_Singleton {
	/**
	 * @param t3lib_TCEmain $parent
	 * @return boolean Whether sanitazion was performed
	 */
	public function sanitizeDataMap(t3lib_TCEmain $parent) {
		// No action, if on live workspace:
		if ($parent->BE_USER->workspace === 0 || $parent->isOuterMostInstance() === FALSE) {
			return FALSE;
		}

		foreach ($this->getSanitationServices($parent) as $sanitazionService) {
			$sanitazionService->sanitize();
		}

		// Pre-process data map:
		foreach ($parent->datamap as $table => $records) {
			// If table does not have elementy (anymore), drop it:
			if (empty($parent->datamap[$table])) {
				unset($parent->datamap[$table]);
			}
		}

		return TRUE;
	}

	/**
	 * @param t3lib_TCEmain $parent
	 * @return Tx_IrreWorkspaces_Service_SanitazionService[]
	 */
	protected function getSanitationServices(t3lib_TCEmain $parent) {
		$sanitazionServices = array();

		$dependency = $this->getDependencyService()->create();

		foreach ($parent->datamap as $table => $records) {
			foreach ($records as $uid => $fields) {
				if (t3lib_div::testInt($uid)) {
					$data = array(
						Tx_IrreWorkspaces_Service_ComparisonService::KEY_Modification => $fields
					);
					$dependency->addElement($table, $uid, $data);
				}
			}
		}

		foreach ($dependency->getOuterMostParents() as $outerMostParent) {
			$sanitazionServices[] = $this->getSanitazionService($parent, $outerMostParent);
		}

		return $sanitazionServices;
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_DependencyService
	 */
	protected function getDependencyService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_DependencyService');
	}

	/**
	 * @param t3lib_TCEmain $parent
	 * @param t3lib_utility_Dependency_Element $outerMostParent
	 * @return Tx_IrreWorkspaces_Service_SanitazionService
	 */
	protected function getSanitazionService(t3lib_TCEmain $parent, t3lib_utility_Dependency_Element $outerMostParent) {
		return t3lib_div::makeInstance(
			'Tx_IrreWorkspaces_Service_SanitazionService',
			$parent,
			$outerMostParent
		);
	}
}

?>