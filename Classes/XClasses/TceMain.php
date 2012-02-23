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
class ux_t3lib_TceMain extends t3lib_TCEmain {
	/**
	 * Pre-processes the dataMap.
	 *
	 * @todo Introduce new hook "processDatamap_beforeStart" similar to process_cmdmap()
	 */
	public function process_datamap() {
		$this->getTceMainService()->sanitizeDataMap($this);
		parent::process_datamap();
	}

	/**
	 * Processing/Preparing content for copyRecord() function
	 *
	 * @param	string		$table Table name
	 * @param	integer		$uid Record uid
	 * @param	string		$field Field name being processed
	 * @param	string		$value Input value to be processed.
	 * @return	string
	 */
	public function copyRecord_procBasedOnFieldType($table, $uid, $field, $value) {
		if ($this->getTceMainService()->forwardCopyRecord($table, $uid, $field, $value, $this)) {
			$value = call_user_func_array(
				'parent::copyRecord_procBasedOnFieldType',
				func_get_args()
			);
		}

		return $value;
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_TceMainService
	 */
	protected function getTceMainService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_TceMainService');
	}
}

?>