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
class Tx_IrreWorkspaces_Service_Record_DeviationService implements t3lib_Singleton {
	const STATE_New = 'new';
	const STATE_Deleted = 'deleted';
	const STATE_Moved = 'moved';
	const STATE_Modified = 'modified';

	/**
	 * @var array
	 */
	protected $systemFields = array(
		'fields' => array(
			'uid',
			'pid',
			't3ver_oid',
			't3ver_id',
			't3ver_wsid',
			't3ver_label',
			't3ver_state', // Delete and move placeholder
			't3ver_stage',
			't3ver_count',
			't3ver_tstamp',
			't3ver_move_id', // Move placeholder
		),
		'tcaControlKeys' => array(
			'crdate',
			'cruser_id',
			'deleted',
			'origUid',
			'transOrigDiffSourceField',
			'transOrigPointerField',
			'tstamp',
		),
	);

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return boolean
	 */
	public function hasDeviation(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		$result = FALSE;

		if ($this->isModified($combinedRecord) === FALSE) {
			$result = TRUE;
		} else {
			t3lib_div::loadTCA($combinedRecord->getTable());
			foreach ($this->getRecordFields($combinedRecord) as $field) {
				if ($this->isDeviation($combinedRecord, $field)) {
					$result = TRUE;
				}
			}
		}

		return $result;
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @param string $field
	 * @return boolean
	 */
	protected function isDeviation(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		$result = TRUE;

		if ($this->isSystemField($combinedRecord, $field)) {
			$result = FALSE;
		} elseif ($this->isUndefinedField($combinedRecord, $field)) {
			$result = FALSE;
		} elseif ($this->isEqual($combinedRecord, $field)) {
			$result = FALSE;
		} elseif ($this->isNotRelevant($combinedRecord, $field)) {
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @param string $field
	 * @return boolean
	 */
	protected function isSystemField(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		return in_array($field, $this->getSystemFields($combinedRecord));
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @param string $field
	 * @return boolean
	 */
	protected function isUndefinedField(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		return (in_array($field, $this->getDefinedFields($combinedRecord)) === FALSE);
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @param string $field
	 * @return boolean
	 */
	protected function isEqual(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		$liveRow = $combinedRecord->getLiveRecord()->getRow();
		$versionRow = $combinedRecord->getVersionRecord()->getRow();
		return ($liveRow[$field] === $versionRow[$field]);
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @param string $field
	 * @return boolean
	 */
	protected function isNotRelevant(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		$result = FALSE;
		$fieldDefinition = $this->getFieldDefinition($combinedRecord, $field);

		if (t3lib_div::inList('passthrough,none', $fieldDefinition['type'])) {
			$result = TRUE;
		} elseif ($fieldDefinition['type'] === 'inline' && !empty($fieldDefinition['foreign_field'])) {
			$result = TRUE;
		} elseif (!empty($fieldDefinition['MM'])) {
			$result = TRUE;
		}

		return $result;
	}

	protected function getFieldDefinition(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		$table = $combinedRecord->getTable();
		return $GLOBALS['TCA'][$table]['columns'][$field]['config'];
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return array
	 */
	protected function getRecordFields(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		return array_keys($combinedRecord->getLiveRecord()->getRow());
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return array
	 */
	protected function getDefinedFields(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		$table = $combinedRecord->getTable();
		return array_keys($GLOBALS['TCA'][$table]['columns']);
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return array
	 */
	protected function getSystemFields(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		$systemFields = $this->systemFields['fields'];
		$table = $combinedRecord->getTable();
		$tcaControl = $GLOBALS['TCA'][$table]['ctrl'];

		foreach ($this->systemFields['tcaControlKeys'] as $key) {
			if (!empty($tcaControl[$key])) {
				 $field = $tcaControl[$key];
				if (!in_array($field, $systemFields)) {
					$systemFields[] = $field;
				}
			}
		}

		return $systemFields;
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return boolean
	 */
	protected function isModified(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		$versionState = $this->getVersionState($combinedRecord);
		return ($versionState === self::STATE_Modified);
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return NULL|string
	 */
	protected function getVersionState(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		$versionState = NULL;
		$versionRow = $combinedRecord->getVersionRecord()->getRow();

		if (isset($versionRow['t3ver_state'])) {
			switch ($versionRow['t3ver_state']) {
				case -1:
					$versionState = self::STATE_New;
					break;
				case 1:
				case 2:
				$versionState = self::STATE_Deleted;
					break;
				case 4:
					$versionState = self::STATE_Moved;
					break;
				default:
					$versionState = self::STATE_Modified;
			}
		}

		return $versionState;
	}
}

?>