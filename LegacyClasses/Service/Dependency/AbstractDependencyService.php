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
abstract class Tx_IrreWorkspaces_Service_Dependency_AbstractDependencyService implements t3lib_Singleton {
	/**
	 * @var t3lib_TCEmain
	 */
	protected $tceMainHelper;

	/**
	 * @var t3lib_utility_Dependency
	 */
	protected $dependency;

	/**
	 * @return t3lib_utility_Dependency
	 */
	public function getDependency() {
		if (!isset($this->dependency)) {
			/** @var $dependency t3lib_utility_Dependency */
			$this->dependency = t3lib_div::makeInstance('t3lib_utility_Dependency');
			$this->dependency->setOuterMostParentsRequireReferences(TRUE);

			$this->dependency->setEventCallback(
				t3lib_utility_Dependency_Element::EVENT_CreateChildReference,
				$this->getDependencyCallback('createNewDependentElementChildReferenceCallback')
			);

			$this->dependency->setEventCallback(
				t3lib_utility_Dependency_Element::EVENT_CreateParentReference,
				$this->getDependencyCallback('createNewDependentElementParentReferenceCallback')
			);
		}

		return $this->dependency;
	}

	/**
	 * Gets a new callback to be used in the dependency resolver utility.
	 *
	 * @param string $method
	 * @param array $targetArguments
	 * @return t3lib_utility_Dependency_Callback
	 */
	protected function getDependencyCallback($method, array $targetArguments = array()) {
		return t3lib_div::makeInstance('t3lib_utility_Dependency_Callback', $this, $method, $targetArguments);
	}

	/**
	 * @return t3lib_TCEmain
	 */
	protected function getTceMainHelper() {
		if (!isset($this->tceMainHelper)) {
			$this->tceMainHelper = t3lib_div::makeInstance('t3lib_TCEmain');
		}
		return $this->tceMainHelper;
	}

	/**
	 * Callback to determine whether a new child reference shall be considered in the dependency resolver utility.
	 *
	 * @param array $callerArguments
	 * @param array $targetArgument
	 * @param t3lib_utility_Dependency_Element $caller
	 * @param string $eventName
	 * @return NULL|string Skip response (if required)
	 */
	public function createNewDependentElementChildReferenceCallback(array $callerArguments, array $targetArgument, t3lib_utility_Dependency_Element $caller, $eventName) {
		/** @var $reference t3lib_utility_Dependency_Reference */
		$reference = $callerArguments['reference'];
		$fieldConfiguration = t3lib_BEfunc::getTcaFieldConfiguration($caller->getTable(), $reference->getField());

		if (!$this->isReferenceInlineField($fieldConfiguration)) {
			return t3lib_utility_Dependency_Element::RESPONSE_Skip;
		}

		return NULL;
	}

	/**
	 * Callback to determine whether a new parent reference shall be considered in the dependency resolver utility.
	 *
	 * @param array $callerArguments
	 * @param array $targetArgument
	 * @param t3lib_utility_Dependency_Element $caller
	 * @param string $eventName
	 * @return NULL|string Skip response (if required)
	 */
	public function createNewDependentElementParentReferenceCallback(array $callerArguments, array $targetArgument, t3lib_utility_Dependency_Element $caller, $eventName) {
		/** @var $reference t3lib_utility_Dependency_Reference */
		$reference = $callerArguments['reference'];
		$fieldConfiguration = t3lib_BEfunc::getTcaFieldConfiguration($reference->getElement()->getTable(), $reference->getField());

		if (!$this->isReferenceInlineField($fieldConfiguration)) {
			return t3lib_utility_Dependency_Element::RESPONSE_Skip;
		}

		return NULL;
	}

	/**
	 * @param array $fieldConfiguration
	 * @return boolean
	 */
	protected function isReferenceInlineField(array $fieldConfiguration) {

		return (
			$fieldConfiguration &&
			t3lib_div::inList('field,list', $this->getTceMainHelper()->getInlineFieldType($fieldConfiguration))
		);
	}
}

?>