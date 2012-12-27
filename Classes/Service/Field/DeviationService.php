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
class Tx_IrreWorkspaces_Service_Field_DeviationService implements t3lib_Singleton {
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
			'sortby',
		),
	);

	/**
	 * @param string $table
	 * @param string $field
	 * @param array $liveRecord
	 * @param array $versionRecord
	 * @return boolean
	 */
	public function isDeviation($table, $field, array $liveRecord, array $versionRecord) {
		$result = TRUE;

		if ($this->isSystemField($table, $field)) {
			$result = FALSE;
		} elseif ($this->isUndefinedField($table, $field)) {
			$result = FALSE;
		} elseif ($this->isEqual($field, $liveRecord, $versionRecord)) {
			$result = FALSE;
		} elseif ($this->isNotRelevant($table, $field)) {
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	public function isSystemField($table, $field) {
		return in_array($field, $this->getSystemFields($table));
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	public function isUndefinedField($table, $field) {
		return (in_array($field, $this->getDefinedFields($table)) === FALSE);
	}

	/**
	 * Determines whether a field is of type file.
	 *
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	public function isFileField($table, $field) {
		$fieldDefinition = $this->getFieldDefinition($table, $field);
		return ($fieldDefinition['type'] == 'group' && $fieldDefinition['internal_type'] == 'file');
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	public function isInlineField($table, $field) {
		$fieldDefinition = $this->getFieldDefinition($table, $field);
		return ($fieldDefinition['type'] === 'inline' && !empty($fieldDefinition['foreign_field']));
	}

	/**
	 * @param string $field
	 * @param array $liveRecord
	 * @param array $versionRecord
	 * @return boolean
	 */
	public function isEqual($field, array $liveRecord, array $versionRecord) {
		return ((string) $liveRecord[$field] === (string) $versionRecord[$field]);
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return boolean
	 */
	public function isNotRelevant($table, $field) {
		$result = FALSE;
		$fieldDefinition = $this->getFieldDefinition($table, $field);

		if (t3lib_div::inList('passthrough,none', $fieldDefinition['type'])) {
			$result = TRUE;
		} elseif ($this->isInlineField($table, $field)) {
			$result = TRUE;
		} elseif (!empty($fieldDefinition['MM'])) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * @param string $table
	 * @param string $field
	 * @return string
	 */
	protected function getFieldDefinition($table, $field) {
		return $GLOBALS['TCA'][$table]['columns'][$field]['config'];
	}

	/**
	 * @param string $table
	 * @return array
	 */
	protected function getDefinedFields($table) {
		return array_keys($GLOBALS['TCA'][$table]['columns']);
	}

	/**
	 * @param string $table
	 * @return array
	 */
	protected function getSystemFields($table) {
		$systemFields = $this->systemFields['fields'];
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
	 * @param array $versionRecord
	 * @return boolean
	 */
	public function isModified(array $versionRecord) {
		$versionState = $this->getVersionState($versionRecord);
		return ($versionState === self::STATE_Modified);
	}

	/**
	 * @param array $versionRecord
	 * @return NULL|string
	 */
	public function getVersionState(array $versionRecord) {
		$versionState = NULL;

		if (isset($versionRecord['t3ver_state'])) {
			switch ($versionRecord['t3ver_state']) {
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