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
class Tx_IrreWorkspaces_Service_ComparisonService {
	const EVENT_PostProcessDifferences = 'postProcessDifferences';
	const KEY_Modification = 'modification';
	const KEY_Origin = 'origin';

	/**
	 * Any (dumb) TCEmain
	 *
	 * @var t3lib_TCEmain
	 */
	protected $tceMain;

	/**
	 * @var t3lib_utility_Dependency_Callback[]
	 */
	protected $eventCallbacks = array();

	/**
	 * @param t3lib_TCEmain $tceMain
	 */
	public function __construct(t3lib_TCEmain $tceMain = NULL) {
		$this->tceMain = $tceMain ?: $this->createTceMain();
	}

	/**
	 * Sets a callback for a particular event.
	 *
	 * @param string $eventName
	 * @param t3lib_utility_Dependency_Callback $callback
	 * @return t3lib_utility_Dependency
	 */
	public function setEventCallback($eventName, t3lib_utility_Dependency_Callback $callback) {
		$this->eventCallbacks[$eventName] = $callback;
	}

	/**
	 * Executes a registered callback (if any) for a particular event.
	 *
	 * @param string $eventName
	 * @param array $callerArguments
	 * @return void
	 */
	protected function executeEventCallback($eventName, array $callerArguments = array()) {
		if (isset($this->eventCallbacks[$eventName])) {
			/** @var $callback t3lib_utility_Dependency_Callback */
			$callback = $this->eventCallbacks[$eventName];
			$callback->execute($callerArguments, $this, $eventName);
		}
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return boolean
	 */
	public function hasDifferences(t3lib_utility_Dependency_Element $element) {
		// Calculate differences between database and submission:
		$differences = $this->resolveDifferences(
			$element,
			array_diff_assoc(
				$this->getWorkspaceData($element),
				$this->getLiveData($element)
			)
		);

		$this->executeEventCallback(
			self::EVENT_PostProcessDifferences,
			array(
				'element' => $element,
				'differences' => &$differences,
			)
		);

		return (count($differences) > 0);
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @param array $differences
	 * @return boolean
	 */
	protected function resolveDifferences(t3lib_utility_Dependency_Element $element, array $differences) {
		foreach ($differences as $field => $value) {
			if ($this->isInlineField($element, $field)) {
				if ($this->canResolveDifferentInlineReferences($element, $field)) {
					unset($differences[$field]);
				}
			}
		}

		return $differences;
	}

	/**
	 * @param t3lib_utility_Dependency_Element
	 * @param string $field
	 * @return boolean
	 */
	protected function canResolveDifferentInlineReferences(t3lib_utility_Dependency_Element $element, $field) {
		$configuration = $this->getTcaConfiguration($element, $field);

		/* @var $dbAnalysis t3lib_loadDBGroup */
		$dbAnalysis = t3lib_div::makeInstance('t3lib_loadDBGroup');
		$dbAnalysis->start(
			$element->getDataValue($field),
			$configuration['foreign_table'],
			$configuration['MM'] ?: '',
			$element->getId(),
			$element->getTable(),
			$configuration
		);

		$submittedItems = t3lib_div::trimExplode(',', $element->getDataValue($field));
		$storedItems = $dbAnalysis->getValueArray();

		$diffenreces = array_merge(
			array_diff($submittedItems, $storedItems),
			array_diff($storedItems, $submittedItems)
		);

		return (count($diffenreces) === 0);
	}

	/**
	 * @param t3lib_utility_Dependency_Element
	 * @param string $field
	 * @return boolean
	 */
	protected function isInlineField(t3lib_utility_Dependency_Element $element, $field) {
		$configuration = $this->getTcaConfiguration($element, $field);

		return (
			$configuration !== FALSE &&
			$this->tceMain->getInlineFieldType($configuration) !== FALSE
		);
	}

	/**
	 * @param t3lib_utility_Dependency_Element
	 * @param string $field
	 * @return boolean|array
	 */
	public function getTcaConfiguration(t3lib_utility_Dependency_Element $element, $field) {
		$configuration = FALSE;

		t3lib_div::loadTCA($element->getTable());
		if (!empty($GLOBALS['TCA'][$element->getTable()]['columns'][$field]['config'])) {
			$configuration = $GLOBALS['TCA'][$element->getTable()]['columns'][$field]['config'];
		}

		return $configuration;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return array
	 */
	protected function getLiveData(t3lib_utility_Dependency_Element $element) {
		if ($this->hasOriginData($element)) {
			$liveData = $element->getDataValue(
				self::KEY_Origin
			);
		} else {
			$liveData = $element->getRecord();
		}

		return $liveData;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return array
	 */
	protected function getWorkspaceData(t3lib_utility_Dependency_Element $element) {
		if ($this->hasModificationData($element)) {
			$workspaceData = $element->getDataValue(
				self::KEY_Modification
			);
		} else {
			$workspaceData = $element->getRecord();
		}

		return $workspaceData;
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return boolean
	 */
	protected function hasOriginData(t3lib_utility_Dependency_Element $element) {
		return $element->hasDataValue(self::KEY_Origin);
	}

	/**
	 * @param t3lib_utility_Dependency_Element $element
	 * @return boolean
	 */
	protected function hasModificationData(t3lib_utility_Dependency_Element $element) {
		return $element->hasDataValue(self::KEY_Modification);
	}

	/**
	 * @return t3lib_TCEmain
	 */
	protected function createTceMain() {
		return t3lib_div::makeInstance('t3lib_TCEmain');
	}
}

?>