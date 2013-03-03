<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Oliver Hader <oliver.hader@typo3.org>
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
class Tx_IrreWorkspaces_Service_SessionService implements t3lib_Singleton {
	const DEFAULT_Stage = 'all';

	/**
	 * @return Tx_IrreWorkspaces_Service_SessionService
	 */
	static public function getInstance() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_SessionService');
	}

	/**
	 * @return string|integer
	 */
	public function getStage() {
		$stage = self::DEFAULT_Stage;

		$backendUser = $this->getBackendUser();
		$workspaceId = $backendUser->workspace;

		if (isset($backendUser->uc['moduleData']['Tx_IrreWorkspaces'][$workspaceId]['stage'])) {
			$stage = $backendUser->uc['moduleData']['Tx_IrreWorkspaces'][$workspaceId]['stage'];
		}

		return $stage;
	}

	/**
	 * @param string|integer $stage
	 */
	public function setStage($stage) {
		if (t3lib_div::testInt($stage) === FALSE && $stage !== self::DEFAULT_Stage) {
			$stage = self::DEFAULT_Stage;
		}

		$backendUser = $this->getBackendUser();
		$workspaceId = $backendUser->workspace;

		$backendUser->uc['moduleData']['Tx_IrreWorkspaces'][$workspaceId]['stage'] = $stage;

		$backendUser->writeUC();
	}

	/**
	 * @return t3lib_beUserAuth
	 */
	public function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}
}

?>