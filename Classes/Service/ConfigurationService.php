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
class Tx_IrreWorkspaces_Service_ConfigurationService implements t3lib_Singleton {
	const KEY_NotificationSubject = 'notificationSubject';
	const KEY_NotificationMessageTemplate = 'notificationMessageTemplate';
	const KEY_EnableFlexFormRendering = 'enableFlexFormRendering';
	const KEY_EnableRecordReduction = 'enableRecordReduction';
	const KEY_EnableRecordDetailReduction = 'enableRecordDetailReduction';
	const KEY_EnableAlternativeNotification = 'enableAlternativeNotification';
	const KEY_EnablePageTreeUpdateOnEditing = 'enablePageTreeUpdateOnEditing';
	const KEY_EnableCache = 'enableCache';

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @return Tx_IrreWorkspaces_Service_ConfigurationService
	 */
	static public function getInstance() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_ConfigurationService');
	}

	/**
	 * Creates this object.
	 */
	public function __construct() {
		if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['irre_workspaces'])) {
			$this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['irre_workspaces']);
		}
	}

	/**
	 * @return NULL|string
	 */
	public function getNotificationSubject() {
		return $this->get(self::KEY_NotificationSubject);
	}

	/**
	 * @return NULL|string
	 */
	public function getNotificationMessageTemplate() {
		return $this->get(self::KEY_NotificationMessageTemplate);
	}

	/**
	 * @return boolean
	 */
	public function getEnableFlexFormRendering() {
		return (bool) $this->get(self::KEY_EnableFlexFormRendering);
	}

	/**
	 * @return boolean
	 */
	public function getEnableRecordReduction() {
		return (bool) $this->get(self::KEY_EnableRecordReduction);
	}

	/**
	 * @return boolean
	 */
	public function getEnableRecordDetailReduction() {
		return (bool) $this->get(self::KEY_EnableRecordDetailReduction);
	}

	/**
	 * @return boolean
	 */
	public function getEnableAlternativeNotification() {
		return (bool) $this->get(self::KEY_EnableAlternativeNotification);
	}

	/**
	 * @return boolean
	 */
	public function getEnablePageTreeUpdateOnEditing() {
		return (bool) $this->get(self::KEY_EnablePageTreeUpdateOnEditing);
	}

	/**
	 * @return boolean
	 */
	public function getEnableCache() {
		return (bool) $this->get(self::KEY_EnableCache);
	}

	/**
	 * @param string $key
	 * @return NULL|string
	 */
	public function get($key) {
		$value = NULL;

		if (isset($this->configuration[$key])) {
			$value = $this->configuration[$key];
		}

		return $value;
	}
}

?>