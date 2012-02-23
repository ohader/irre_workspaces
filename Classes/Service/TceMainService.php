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
	 * @var array
	 */
	protected $recordsCache = array();

	/**
	 * @var t3lib_TCEmain
	 */
	protected $anyTceMain;

	public function sanitizeDataMap(t3lib_TCEmain $parent) {
		$this->anyTceMain = $parent;

		// No action, if on live workspace:
		if ($parent->BE_USER->workspace === 0) {
			return FALSE;
		}

		// Pre-process data map:
		foreach ($parent->datamap as $table => $records) {
			foreach ($records as $uid => $fields) {
				// Non-Integer values are new records:
				if (t3lib_div::testInt($uid) === FALSE) {
					continue;
				}

				// Calculate differences between database and submission:
				$differences = array_diff_assoc(
					$fields,
					$this->getRecord($table, $uid)
				);

				// If differences can be resolves as unmodifies IRRE child nodes:
				if (!$differences || $this->canRejectDifferences($table, $uid, $differences)) {
					unset($parent->datamap[$table][$uid]);
				}
			}

			// If table does not have elementy (anymore), drop it:
			if (empty($parent->datamap[$table])) {
				unset($parent->datamap[$table]);
			}
		}
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @param array $differences
	 * @return boolean
	 */
	protected function canRejectDifferences($table, $uid, array $differences) {
		foreach ($differences as $field => $value) {
			if ($this->isInlineField($table, $field)) {
				if ($this->hasDifferentReferences($table, $uid, $field, $value) === FALSE) {
					unset($differences[$field]);
				}
			}
		}

		return count($differences) === 0;
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @return NULL|array
	 */
	protected function getRecord($table, $uid) {
		if (!isset($this->recordsCache[$table][$uid])) {
			$this->recordsCache[$table][$uid] = t3lib_BEfunc::getRecord($table, $uid);
		}
		return $this->recordsCache[$table][$uid];
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	protected function isInlineField($table, $field) {
		$configuration = $this->getTcaConfiguration($table, $field);

		return (
			$configuration !== FALSE &&
			$this->anyTceMain->getInlineFieldType($configuration) !== FALSE
		);
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return boolean|array
	 */
	protected function getTcaConfiguration($table, $field) {
		$configuration = FALSE;

		t3lib_div::loadTCA($table);
		if (!empty($GLOBALS['TCA'][$table]['columns'][$field]['config'])) {
			$configuration = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
		}

		return $configuration;
	}

	/**
	 * @param string $table
	 * @param integer $uid
	 * @param string $field
	 * @param string $value
	 * @return boolean
	 */
	protected function hasDifferentReferences($table, $uid, $field, $value) {
		$configuration = $this->getTcaConfiguration($table, $field);
		$inlineType = $this->anyTceMain->getInlineFieldType($configuration);

		if ($inlineType === FALSE) {
			return TRUE;
		}

		$allowedTables = $configuration['foreign_table'];
		$mmTable = $configuration['MM'] ?: '';

		/* @var $dbAnalysis t3lib_loadDBGroup */
		$dbAnalysis = t3lib_div::makeInstance('t3lib_loadDBGroup');
		$dbAnalysis->start($value, $allowedTables, $mmTable, $uid, $table, $configuration);

		$submittedItems = t3lib_div::trimExplode(',', $value);
		$storedItems = $dbAnalysis->getValueArray();

		$diffenreces = array_merge(
			array_diff($submittedItems, $storedItems),
			array_diff($storedItems, $submittedItems)
		);

		return (count($diffenreces) > 0);
	}
}

?>