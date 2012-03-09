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
class Tx_IrreWorkspaces_Service_BehaviourService implements t3lib_Singleton {
	const RecipientMode_All = 0;
	const RecipientMode_Editor = 1;
	const RecipientMode_EditorAndOwner = 2;
	const RecipientMode_None = 9;

	const Field_WorkspaceAction_RecipientMode = 'tx_irreworkspaces_action_recipient_mode';
	const Field_StageEditing_RecipientMode = 'tx_irreworkspaces_stage_editing_recipient_mode';
	const Field_StagePublish_RecipientMode = 'tx_irreworkspaces_stage_publish_recipient_mode';
	const Field_StageAny_RecipientMode = 'tx_irreworkspaces_recipient_mode';

	/**
	 * @var Tx_IrreWorkspaces_Domain_Model_Record[]
	 */
	protected $affectedRecords;

	/**
	 * @var array
	 */
	protected $stageRecords = array();

	/**
	 * @var array
	 */
	protected $workspaceRecords = array();

	public function setAffectedRecords(array $affectedRecords = NULL) {
		$this->affectedRecords = $affectedRecords;
	}

 	public function getActionRecipients($workspaceId) {

	}

	public function getStageRecipients($stageId, array $regularRecipients = array()) {
		switch ($this->getStageRecipientMode($stageId)) {
			case self::RecipientMode_None:
				return $this->convertRecipientsToCheckboxItems($regularRecipients, FALSE);
				break;
			case self::RecipientMode_Editor:
				break;
			case self::RecipientMode_EditorAndOwner:
				break;
			case self::RecipientMode_All:
			default:
				return $this->convertRecipientsToCheckboxItems($regularRecipients, TRUE);
		}

		$recipients = $this->getRecipients(
			$this->getStageRecipientMode($stageId)
		);

		foreach ($regularRecipients as $regularRecipient) {
			$userId = $regularRecipient['uid'];

			if (!isset($regularRecipient[$userId])) {
				$recipients[] = $regularRecipient;
			}
		}

		return $recipients;
	}

	protected function getRecipients($recipientMode) {

	}

	protected function getWorkspaceOwner($workspaceId) {

	}

	protected function getElementEditor(array $record) {

	}

	protected function getElementsEditors(array $records) {

	}

	/**
	 * @param integer $workspaceId
	 * @return integer
	 */
	public function getWorkspaceActionRecipientMode($workspaceId = NULL) {
		if ($workspaceId === NULL) {
			$workspaceId = $this->getCurrentWorkspaceId();
		}

		return $this->getArrayValue(
			$this->getWorkspaceRecord($workspaceId),
			self::Field_WorkspaceAction_RecipientMode,
			0
		);
	}

	/**
	 * @param integer $stageId
	 * @return integer
	 */
	public function getStageRecipientMode($stageId) {
		switch ($stageId) {
			case Tx_Workspaces_Service_Stages::STAGE_PUBLISH_EXECUTE_ID:
				return $this->getStagePublishRecipientMode();
				break;
			case Tx_Workspaces_Service_Stages::STAGE_PUBLISH_ID:
				return $this->getStagePublishRecipientMode();
				break;
			case Tx_Workspaces_Service_Stages::STAGE_EDIT_ID:
				return $this->getStageEditingRecipientMode();
				break;
			default:
				return $this->getStageAnyRecipientMode($stageId);
		}
	}

	/**
	 * @param integer $workspaceId
	 * @return integer
	 */
	protected function getStageEditingRecipientMode($workspaceId = NULL) {
		if ($workspaceId === NULL) {
			$workspaceId = $this->getCurrentWorkspaceId();
		}

		return $this->getArrayValue(
			$this->getWorkspaceRecord($workspaceId),
			self::Field_StageEditing_RecipientMode,
			0
		);
	}

	/**
	 * @param integer $workspaceId
	 * @return integer
	 */
	protected function getStagePublishRecipientMode($workspaceId = NULL) {
		if ($workspaceId === NULL) {
			$workspaceId = $this->getCurrentWorkspaceId();
		}

		return $this->getArrayValue(
			$this->getWorkspaceRecord($workspaceId),
			self::Field_StagePublish_RecipientMode,
			0
		);
	}

	/**
	 * @param integer $stageId
	 * @return integer
	 */
	protected function getStageAnyRecipientMode($stageId) {
		return $this->getArrayValue(
			$this->getStageRecord($stageId),
			self::Field_StageAny_RecipientMode,
			0
		);
	}

	/**
	 * @param array $recipients
	 * @param boolean $checked
	 * @return array
	 */
	public function convertRecipientsToCheckboxItems(array $recipients, $checked = FALSE) {
		$checkboxItems = array();

		foreach ($recipients as $recipient) {
			$checkboxItems[] = $this->createRecipientCheckboxItem($recipient, $checked);
		}

		return $checkboxItems;
	}

	/**
	 * @param array $record
	 * @param boolean $checked
	 * @return array
	 */
	protected function createRecipientCheckboxItem(array $record, $checked = FALSE) {
		$name = ($record['realName'] ? $record['realName'] : $record['username']);

		return array(
			'boxLabel' => sprintf('%s (%s)', $name, $record['email']),
			'name' => 'receipients-' . $record['uid'],
			'checked' => (bool) $checked,
		);
	}


	/**
	 * @return integer
	 */
	protected function getCurrentWorkspaceId() {
		return $this->getBackendUser()->workspace;
	}

	/**
	 * @return t3lib_beUserAuth
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @param string $key
	 * @param array $array
	 * @param mixed $default
	 * @return mixed
	 */
	protected function getArrayValue($key, array $array, $default = NULL) {
		$value = $default;

		if (!empty($array[$key])) {
			$value = $array[$key];
		}

		return $value;
	}

	/**
	 * @param integer $stageId
	 * @return array
	 */
	protected function getStageRecord($stageId) {
		if (!isset($this->stageRecords[$stageId])) {
			$this->stageRecords[$stageId] = t3lib_BEfunc::getRecord('sys_workspace_stage', $stageId);
		}

		return $this->stageRecords[$stageId];
	}

	/**
	 * @param integer $workspaceId
	 * @return array
	 */
	protected function getWorkspaceRecord($workspaceId) {
		if (!isset($this->workspaceRecords[$workspaceId])) {
			$this->workspaceRecords[$workspaceId] = t3lib_BEfunc::getRecord('sys_workspace', $workspaceId);
		}

		return $this->workspaceRecords[$workspaceId];
	}
}

?>