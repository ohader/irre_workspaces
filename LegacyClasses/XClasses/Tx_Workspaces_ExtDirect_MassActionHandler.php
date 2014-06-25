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
class Ux_Tx_Workspaces_ExtDirect_MassActionHandler extends Tx_Workspaces_ExtDirect_MassActionHandler {
	/**
	 * Publishes the current workspace.
	 *
	 * @param stdClass $parameters
	 * @return array
	 */
	public function publishWorkspace(stdclass $parameters) {
		if (isset($parameters->stage) && t3lib_div::testInt($parameters->stage)) {
			$this->getWorkspaceService()->setStage($parameters->stage);
		}

		$result = parent::publishWorkspace($parameters);
		$this->getWorkspaceService()->reset();

		return $result;
	}

	/**
	 * Flushes the current workspace.
	 *
	 * @param stdClass $parameters
	 * @return array
	 */
	public function flushWorkspace(stdclass $parameters) {
		if (isset($parameters->stage) && t3lib_div::testInt($parameters->stage)) {
			$this->getWorkspaceService()->setStage($parameters->stage);
		}

		$result = parent::flushWorkspace($parameters);
		$this->getWorkspaceService()->reset();

		return $result;
	}

	/**
	 * Gets an instance of the workspaces service.
	 *
	 * @return Tx_IrreWorkspaces_Service_Alternative_WorkspaceService
	 */
	protected function getWorkspaceService() {
		return Tx_IrreWorkspaces_Service_Alternative_WorkspaceService::getInstance();
	}
}

?>