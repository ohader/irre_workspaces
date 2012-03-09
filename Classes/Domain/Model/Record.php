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
class Tx_IrreWorkspaces_Domain_Model_Record {
	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var integer
	 */
	protected $uid;

	/**
	 * @var array
	 */
	protected $record;

	/**
	 * @param mixed $data
	 * @return Tx_IrreWorkspaces_Domain_Model_Record
	 * @throws RuntimeException
	 */
	public static function create($data) {
		if ($data instanceof stdClass) {
			return t3lib_div::makeInstance(
				'Tx_IrreWorkspaces_Domain_Model_Record',
				$data->table,
				$data->uid
			);
		}

		throw new RuntimeException('Unknow data type');
	}

	public function __construct($table, $uid) {
		$this->table = (string) $table;
		$this->uid = (int) $uid;
	}

	/**
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * @return integer
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * @return array
	 */
	public function getRecord() {
		if (!isset($this->record)) {
			$this->record = $this->getDatabase()->exec_SELECTgetSingleRow(
				'*',
				$this->getTable(),
				'uid=' . $this->getUid()
			);
		}

		return $this->record;
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}
}

?>