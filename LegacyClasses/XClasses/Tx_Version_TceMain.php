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
class ux_tx_version_tcemain extends tx_version_tcemain {
	/**
	 * hook that is called AFTER all commands of the commandmap was
	 * executed
	 *
	 * @param t3lib_TCEmain $tcemainObj reference to the main tcemain object
	 * @return void
	 */
	public function processCmdmap_afterFinish(t3lib_TCEmain $tcemainObj) {
		$this->getFlushWorkspaceActionService()->finish($tcemainObj);
		$this->getPublishWorkspaceActionService()->finish($tcemainObj);

		/**
		 * Call remapping actions again, since they have been processed prior to this hook
		 * @todo Introduce new new processCmdmap_beforeFinish hook in TCEmain
		 */
		$tcemainObj->remapListedDBRecords();
		$tcemainObj->processRemapStack();

		parent::processCmdmap_afterFinish($tcemainObj);
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Action_FlushWorkspaceActionService
	 */
	protected function getFlushWorkspaceActionService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Action_FlushWorkspaceActionService');
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService
	 */
	protected function getPublishWorkspaceActionService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Action_PublishWorkspaceActionService');
	}
}

?>