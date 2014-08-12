<?php
namespace OliverHader\IrreWorkspaces;

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
class Bootstrap {

	/**
	 * Registers hooks
	 */
	public static function registerHooks() {
		// Hook to store original request URL during login
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['irre_workspaces'] =
			'OliverHader\\IrreWorkspaces\\Service\\RedirectService->handle';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']['irre_workspaces'] =
			'OliverHader\\IrreWorkspaces\\Service\\RedirectService->fetch';
	}

	/**
	 * Registers hooks
	 */
	public static function registerLegacyHooks() {
		return;

		// Modify the bahaviour of the workspace module

		// Hook to add additional stylesheet and JavaScript resources to Workspaces Module:
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['irre_workspaces'] =
			'EXT:irre_workspaces/Classes/Hooks/ReviewControllerResourcesHook.php:Tx_IrreWorkspaces_Hooks_ReviewControllerResourcesHook->renderPreProcess';

		// Modify the bahaviour of the workspace module

		// Hook to modify the differences view and exclude e.g. l10n_diffsource fields:
		if (self::getConfigurationService()->getEnableRecordDetailReduction()) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['workspaces']['modifyDifferenceArray']['irre_workspaces'] =
				'EXT:irre_workspaces/Classes/Hooks/ExtDirectServerHook.php:Tx_IrreWorkspaces_Hooks_ExtDirectServerHook';
		}

		// Hook to pre- and post-process values (required for FlexForm rendering)
		if (self::getConfigurationService()->getEnableFlexFormRendering()) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['workspaces']['modifyDifferenceArray']['irre_workspaces'] =
				'EXT:irre_workspaces/Classes/Hooks/ExtDirectServerHook.php:Tx_IrreWorkspaces_Hooks_ExtDirectServerHook';
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue']['irre_workspaces'] =
				'EXT:irre_workspaces/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook->preProcess';
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue']['irre_workspaces'] =
				'EXT:irre_workspaces/Classes/Hooks/ValueProcessingHook.php:Tx_IrreWorkspaces_Hooks_ValueProcessingHook->postProcess';
		}

		// Hooks to visualize current target page if editing a workspace element
		if (self::getConfigurationService()->getEnablePageTreeUpdateOnEditing()) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/alt_doc.php']['makeEditForm_accessCheck']['irre_workspaces'] =
				'EXT:irre_workspaces/Classes/Hooks/PageTreeVisualizationHook.php:Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook->handleEditing';
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['updateSignalHook']['tx_irreworkspaces::updateEditing'] =
				'EXT:irre_workspaces/Classes/Hooks/PageTreeVisualizationHook.php:Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook->updateEditing';
		}

		// Hook to store original request URL during login
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['irre_workspaces'] =
			'EXT:irre_workspaces/Classes/Service/RedirectService.php:Tx_IrreWorkspaces_Service_RedirectService->handle';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']['irre_workspaces'] =
			'EXT:irre_workspaces/Classes/Service/RedirectService.php:Tx_IrreWorkspaces_Service_RedirectService->fetch';
	}

	/**
	 * Registers slots (signal-slot-dispatcher)
	 */
	public static function registerSlots() {
		if (self::getConfigurationService()->getInstance()->getEnableRecordReduction()) {
			self::getSignalSlotDispatcher()->connect(
				'TYPO3\\CMS\\Workspaces\\Service\\GridDataService',
				\TYPO3\CMS\Workspaces\Service\GridDataService::SIGNAL_GenerateDataArray_PostProcesss,
				'OliverHader\\IrreWorkspaces\\Slot\\GridDataSlot',
				'postProcess'
			);
		}
	}

	/**
	 * Registers alternative implementations (XCLASS)
	 */
	public static function registerAlternatives() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Version\\Hook\\DataHandlerHook'] = array(
			'className' => 'OliverHader\\IrreWorkspaces\\Alternative\\DataHandlerHook'
		);
	}

	/**
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected static function getSignalSlotDispatcher() {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher'
		);
	}

	/**
	 * @return Service\ConfigurationService
	 */
	protected static function getConfigurationService() {
		return \OliverHader\IrreWorkspaces\Service\ConfigurationService::getInstance();
	}

}

?>