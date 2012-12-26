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
	 * @return boolean
	 */
	public function isModified(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		return $this->getFieldDeviationService()->isModified(
			$combinedRecord->getVersionRecord()->getRow()
		);
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @param string $field
	 * @return boolean
	 */
	protected function isDeviation(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord, $field) {
		return $this->getFieldDeviationService()->isDeviation(
			$combinedRecord->getTable(),
			$field,
			$combinedRecord->getLiveRecord()->getRow(),
			$combinedRecord->getVersionRecord()->getRow()
		);
	}

	/**
	 * @param Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord
	 * @return array
	 */
	protected function getRecordFields(Tx_Workspaces_Domain_Model_CombinedRecord $combinedRecord) {
		return array_keys($combinedRecord->getLiveRecord()->getRow());
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Field_DeviationService
	 */
	protected function getFieldDeviationService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Field_DeviationService');
	}
}

?>