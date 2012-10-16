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
class Tx_IrreWorkspaces_Hooks_ExtDirectServerHook {
	/**
	 * Modifies the differences show in the workspace module
	 * for each single changed element there.
	 *
	 * @param stdClass $parameter
	 * @param array $diffReturnArray
	 * @param array $liveReturnArray
	 * @param t3lib_diff $t3lib_diff
	 */
	public function modifyDifferenceArray($parameter, array &$diffReturnArray, array &$liveReturnArray, t3lib_diff $t3lib_diff) {
		$table = $parameter->table;

		foreach ($diffReturnArray as $index => $data) {
			if ($this->isInternalField($table, $data['field'])) {
				unset($diffReturnArray[$index]);
				unset($liveReturnArray[$index]);
			}
		}
	}

	/**
	 * Determines whether a field is considered to be internal.
	 *
	 * @param string $table Name of the table
	 * @param string $field Name of the field to be checked
	 * @return boolean
	 */
	protected function isInternalField($table, $field) {
		$result = FALSE;

			// Regular system fields:
		if (t3lib_div::inList('uid,pid', $field)) {
			$result = TRUE;

			// Translation differences:
		} elseif (!empty($GLOBALS['TCA'][$table]['ctrl']['transOrigDiffSourceField']) && $field === $GLOBALS['TCA'][$table]['ctrl']['transOrigDiffSourceField']) {
			$result = TRUE;
		}

		return $result;
	}
}

?>