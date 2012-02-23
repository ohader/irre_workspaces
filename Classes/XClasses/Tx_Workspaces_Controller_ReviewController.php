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
class Ux_Tx_Workspaces_Controller_ReviewController extends Tx_Workspaces_Controller_ReviewController {
	/**
	 * @var ux_t3lib_PageRenderer
	 */
	protected $pageRenderer;

	protected function initializeAction() {
		parent::initializeAction();

		$publicResourcesPath = t3lib_extMgm::extRelPath('irre_workspaces') . 'Resources/Public/';

		$this->pageRenderer->disableCompressCss();
		$this->pageRenderer->disableCompressJavascript();
		$this->pageRenderer->addCssFile($publicResourcesPath . 'Stylesheet/Module.css');

		$jsFiles = $this->pageRenderer->getJsFiles();
		$this->pageRenderer->setJsFiles(array());

		$this->pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Controller.js');
		$this->pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Plugin/MultiGrouping.js');

		foreach ($jsFiles as $filePath => $fileConfiguration) {
			$this->pageRenderer->appendJsFile($filePath, $fileConfiguration);

			if (strpos($filePath, '/workspaces/Resources/Public/JavaScript/component.js')) {
				$this->pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Component.js');

			} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/configuration.js')) {
				$this->pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Configuration.js');

			} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/grid.js')) {
				$this->pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Grid.js');

			} elseif (strpos($filePath, '/workspaces/Resources/Public/JavaScript/toolbar.js')) {
				$this->pageRenderer->addJsFile($publicResourcesPath . 'JavaScript/Override/Toolbar.js');
			}
		}
	}
}

?>