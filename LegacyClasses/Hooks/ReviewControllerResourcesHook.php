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
class Tx_IrreWorkspaces_Hooks_ReviewControllerResourcesHook {
	/**
	 * Pre-processes the render action and adds individual resources.
	 *
	 * @param array $parameters
	 * @param t3lib_PageRenderer $pageRenderer
	 */
	public function renderPreProcess(array $parameters, t3lib_PageRenderer $pageRenderer) {
		if ($this->isReviewControllerCall($parameters)) {
			$this->updatePageTreeVisualization();

			$publicResourcesPath = t3lib_extMgm::extRelPath('irre_workspaces') . 'Resources/Public/';

			$jsFiles = $parameters['jsFiles'];
			$parameters['jsFiles'] = array();

			$pageRenderer->addJsFile(
				$publicResourcesPath . 'JavaScript/Helper.js',
				'text/javascript',
				TRUE,
				TRUE
			);

			$pageRenderer->addCssFile($publicResourcesPath . 'Stylesheet/Module.css');
			$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Actions.js');
			$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Controller.js');
			$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Plugin/MultiGrouping.js');

			$pageRenderer->addInlineSetting(
				'TxIrreWorkspaces',
				'enableRecordSinglePublish',
				Tx_IrreWorkspaces_Service_ConfigurationService::getInstance()->getEnableRecordSinglePublish()
			);

			$pageRenderer->addInlineSetting(
				'TxIrreWorkspaces',
				'valueStageSelector',
				Tx_IrreWorkspaces_Service_SessionService::getInstance()->getStage()
			);

			// Adds override files after(!) the original files
			foreach ($jsFiles as $filePath => $fileConfiguration) {
				$parameters['jsFiles'][$filePath] = $fileConfiguration;

				if (strpos($filePath, '/workspaces/Resources/Public/JavaScript/actions.js')) {
					$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Actions.js');

				} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/component.js')) {
					$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Component.js');

				} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/configuration.js')) {
					$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Configuration.js');

				} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/grid.js')) {
					$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Grid.js');

				} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/toolbar.js')) {
					$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Toolbar.js');

				} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/workspaces.js')) {
					$pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Application.js');
				}
			}
		}
	}

	/**
	 * Updates the highlighting in the page tree.
	 *
	 * @return void
	 */
	protected function updatePageTreeVisualization() {
		$pageId = t3lib_div::_GP('id');

		if (t3lib_div::testInt($pageId) && $pageId > 0) {
			t3lib_BEfunc::setUpdateSignal('tx_irreworkspaces::updateEditing', $pageId);
		}
	}

	/**
	 * Determines whether the workspace review controller is called.
	 *
	 * @param array $parameters
	 * @return boolean
	 */
	protected function isReviewControllerCall(array $parameters) {
		return (TYPO3_MODE === 'BE' && t3lib_div::_GP('M') === 'web_WorkspacesWorkspaces');
	}
}

?>