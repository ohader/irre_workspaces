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
class Ux_Tx_Workspaces_ExtDirect_ActionHandler extends tx_Workspaces_ExtDirect_ActionHandler {
	/**
	 * Gets the dialog window to be displayed before a record can be sent to the next stage.
	 *
	 *	@param integer $uid
	 * @param string $table
	 * @param integer $t3ver_oid
	 * @return array
	 */
	public function sendToNextStageWindow($uid, $table, $t3ver_oid) {
		$affectedRecords = array(
			t3lib_div::makeInstance(
				'Tx_IrreWorkspaces_Domain_Model_Record',
				$table,
				$uid
			)
		);

		$this->getBehaviourService()->setAffectedRecords($affectedRecords);

		return parent::sendToNextStageWindow($uid, $table, $t3ver_oid);
	}

	/**
	 * Gets the dialog window to be displayed before a record can be sent to the previous stage.
	 *
	 * @param integer $uid
	 * @param string $table
	 * @return array
	 */
	public function sendToPrevStageWindow($uid, $table) {
		$affectedRecords = array(
			t3lib_div::makeInstance(
				'Tx_IrreWorkspaces_Domain_Model_Record',
				$table,
				$uid
			)
		);

		$this->getBehaviourService()->setAffectedRecords($affectedRecords);

		return parent::sendToPrevStageWindow($uid, $table);
	}

	/**
	 * Gets the dialog window to be displayed before a record can be sent to a specific stage.
	 *
	 * @param integer $nextStageId
	 * @param stdClass $elements
	 * @return array
	 */
	public function sendToSpecificStageWindow($nextStageId, $elements) {
		$affectedRecords = array();

		foreach ($elements as $element) {
			$affectedRecords[] = Tx_IrreWorkspaces_Domain_Model_Record::create($element);
		}

		$this->getBehaviourService()->setAffectedRecords($affectedRecords);

		return parent::sendToSpecificStageWindow($nextStageId);
	}

	/**
	 * Gets all assigned recipients of a particular stage.
	 *
	 * @param integer $stageId
	 * @return array
	 */
	protected function getReceipientsOfStage($stageId) {
		return parent::getReceipientsOfStage($stageId);
#		$regularRecipients = $this->getStageService()->getResponsibleBeUser($stageId);
#		return $this->getBehaviourService()->getStageRecipients($stageId, $regularRecipients);
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_BehaviourService
	 */
	protected function getBehaviourService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_BehaviourService');
	}
}

?>