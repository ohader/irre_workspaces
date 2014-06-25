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
class Tx_IrreWorkspaces_Hooks_PageTreeVisualizationHook {
	/**
	 * @param array $parameters
	 * @param SC_alt_doc $parent
	 * @return integer|boolean
	 */
	public function handleEditing(array $parameters, SC_alt_doc $parent) {
		$pageId = $this->determineEditingPageId($parent);

		if ($pageId !== NULL) {
			t3lib_BEfunc::setUpdateSignal('tx_irreworkspaces::updateEditing', $pageId);
		}

		return $parameters['hasAccess'];
	}

	/**
	 * Updates the page tree state (triggered by getUpdateSignal).
	 *
	 * @param array $parameters
	 */
	public function updateEditing(array &$parameters) {
		$pageId = $parameters['parameter'];

		if (t3lib_div::testInt($pageId)) {
			$parameters['JScode'] = '
				if (top && top.TYPO3.TxIrreWorkspaces.PageTree) {
					top.TYPO3.TxIrreWorkspaces.PageTree.select(' . $pageId . ');
				}
			';
		}
	}

	/**
	 * @param SC_alt_doc $parent
	 * @return integer|NULL
	 */
	protected function determineEditingPageId(SC_alt_doc $parent) {
		$result = NULL;

		$returnUrl = $parent->retUrl;
		if (!empty($returnUrl)) {
			$returnUrlParts = parse_url($returnUrl);
			$returnUrlArguments = t3lib_div::explodeUrl2Array($returnUrlParts['query']);

			if (!empty($returnUrlParts['path'])
				&& strpos($returnUrlParts['path'], TYPO3_mainDir . 'mod.php') !== FALSE
				&& !empty($returnUrlArguments['M']) && $returnUrlArguments['M'] === 'web_WorkspacesWorkspaces') {

				$editRequests = array();

				foreach ($parent->editconf as $table => $ids) {
					foreach ($ids as $id => $action) {
						if (t3lib_div::testInt($id) && $id > 0) {
							$editRequests[] = array(
								'table' => $table,
								'id' => $id,
							);
						}
					}
				}

				if (count($editRequests) === 1) {
					$result = $this->getPageId(
						$editRequests[0]['table'],
						$editRequests[0]['id']
					);
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $table
	 * @param integer $id
	 * @return string|NULL
	 */
	protected function getPageId($table, $id) {
		$pageId = NULL;

		$record = t3lib_BEfunc::getLiveVersionOfRecord($table, $id);
		if (!empty($record) && is_array($record) && isset($record['pid'])) {
			$pageId = $record['pid'];
		}

		return $pageId;
	}
}

?>