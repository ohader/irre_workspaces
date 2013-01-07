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
class Tx_IrreWorkspaces_ExtDirect_ActionHandler extends tx_Workspaces_ExtDirect_AbstractHandler {
	const Permission_Publish = 'publish_access';

	/**
	 * @return array
	 */
	public function getPages() {
		$pages = array();
		$rows = $this->getDatabase()->exec_SELECTgetRows('uid,pid', 'pages', 'deleted=0');

		if ($rows !== NULL) {
			$pages = $rows;
		}

		return array('data' => $pages);
	}

	/**
	 * @param integer $pageId
	 * @return array
	 */
	public function getRootLine($pageId) {
		$pageIds = array();

		foreach (array_reverse(t3lib_BEfunc::BEgetRootLine($pageId)) as $page) {
			$pageId = (int) $page['uid'];
			if ($pageId) {
				$pageIds[] = $pageId;
			}
		}

		return $pageIds;
	}

	/**
	 * Publishes the current workspace.
	 *
	 * @param stdclass $parameters
	 * @return array
	 */
	public function publishWorkspace(stdclass $parameters) {
		$commands = $this->getPublishCommands($parameters->records);
		$result = $this->executeCommands($commands);

		$this->setResultCount($result, count($parameters->records));

		return $result;
	}

	/**
	 * Publishes the current workspace.
	 *
	 * @param stdClass $parameters
	 * @return array
	 */
	public function swapWorkspace(stdClass $parameters) {
		$commands = $this->getSwapCommands($parameters->records);
		$result = $this->executeCommands($commands);

		$this->setResultCount($result, count($parameters->records));

		return $result;
	}

	/**
	 * Flushes the current workspace.
	 *
	 * @param stdClass $parameters
	 * @return array
	 */
	public function flushWorkspace(stdClass $parameters) {
		$commands = $this->getFlushCommands($parameters->records);
		$result = $this->executeCommands($commands);

		$this->setResultCount($result, count($parameters->records));

		return $result;
	}

	/**
	 * @param array $records
	 * @return array
	 */
	protected function getPublishCommands(array $records) {
		$commands = array();

		// if ($this->checkWorkspacePermission($this->getCurrentWorkspace(), self::Permission_Publish)) {
		$commands = $this->getPublishSwapCommands($records, FALSE);
		// }

		return $commands;
	}

	/**
	 * @param array $records
	 * @return array
	 */
	protected function getSwapCommands(array $records) {
		return $this->getPublishSwapCommands($records, TRUE);
	}

	/**
	 * @param array $records
	 * @param boolean $swapIntoWS
	 * @return array
	 */
	protected function getPublishSwapCommands(array $records, $swapIntoWS) {
		$commands = array();

		/** @var $record Tx_IrreWorkspaces_ExtDirect_ParameterRecord */
		foreach ($records as $record) {
			$uid = $record->uid;
			$table = $record->table;
			$t3verOid = $record->t3ver_oid;

			$commands[$table][$t3verOid]['version'] = array(
				'action' => 'swap',
				'swapWith' => $uid,
				'swapIntoWS' => (bool) $swapIntoWS,
			);
		}

		return $commands;
	}

	/**
	 * @param array $records
	 * @return array
	 */
	protected function getFlushCommands(array $records) {
		$commands = array();

		/** @var $record Tx_IrreWorkspaces_ExtDirect_ParameterRecord */
		foreach ($records as $record) {
			$uid = $record->uid;
			$table = $record->table;

			$commands[$table][$uid]['version'] = array(
				'action' => 'clearWSID',
			);
		}

		return $commands;
	}

	/**
	 * @param integer $workspace
	 * @param string $permission
	 * @return boolean
	 */
	protected function checkWorkspacePermission($workspace, $permission) {
		$workspaceRecord = t3lib_BEfunc::getRecord('sys_workspace', intval($workspace));
		return (is_array($workspaceRecord) && $workspaceRecord[$permission] & 1);
	}

	/**
	 * @param array $commands
	 * @return t3lib_TCEmain
	 */
	protected function executeCommands(array $commands) {
		$result = array(
			'error' => 'No records given',
		);

		if (count($commands) > 0) {
			$tceMain = $this->getTceMain();
			$tceMain->start(array(), $commands);
			$tceMain->process_cmdmap();

			if ($tceMain->errorLog) {
				$result['error'] = implode('<br/>', $tceMain->errorLog);
			} else {
				unset($result['error']);
			}
		}

		return $result;
	}

	/**
	 * @return t3lib_TCEmain
	 */
	protected function getTceMain() {
		/** @var $tceMain t3lib_TCEmain */
		$tceMain = t3lib_div::makeInstance('t3lib_TCEmain');
		$tceMain->stripslashes_values = 0;
		return $tceMain;
	}

	/**
	 * @param array $result
	 * @param integer $count
	 */
	protected function setResultCount(&$result, $count) {
		$result['total'] = (int) $count;
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}
}

?>