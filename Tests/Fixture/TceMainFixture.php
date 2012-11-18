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

require_once t3lib_extMgm::extPath('irre_workspaces') . 'Classes/XClasses/Tx_Version_TceMain.php';

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class Tx_Version_TceMainFixture extends ux_tx_version_tcemain {
	/**
	 * @param string $method
	 * @return mixed
	 */
	public function _callMethod($method) {
		$arguments = func_get_args();
		array_shift($arguments);

		return call_user_func_array(
			array($this, $method),
			$arguments
		);
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function _setProperty($property, $value) {
		$this->$property = $value;
	}

	/**
	 * @param string $property
	 * @return mixed
	 */
	public function _getProperty($property) {
		return $this->$property;
	}
}

?>