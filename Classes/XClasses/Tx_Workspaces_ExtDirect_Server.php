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
class Ux_Tx_Workspaces_ExtDirect_Server extends tx_Workspaces_ExtDirect_Server {

	/**
	 * Get List of workspace changes
	 *
	 * @param stdClass $parameters
	 * @return array $data
	 */
	public function getWorkspaceInfos($parameters) {
		if (isset($parameters->stage) && t3lib_div::testInt($parameters->stage)) {
			$this->getWorkspaceService()->setStage($parameters->stage);
		}

		$data = parent::getWorkspaceInfos($parameters);
		$this->getWorkspaceService()->reset();

		return $data;
	}

	/**
	 * Fetches further information to current selected worspace record.
	 *
	 * @param object $parameter
	 * @return array $data
	 */
	public function getRowDetails($parameter) {
		$isTcaModified = FALSE;
		$table = $parameter->table;

		/**
		 * Register a (fake) singleton instance to override t3lib_diff behaviours on rendering large FlexForms
		 * @var $differenceService Tx_IrreWorkspaces_Service_Difference_AlternativeCoreService
		 */
		$differenceService = t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Difference_AlternativeCoreService');
		$differenceService->setUseClearBuffer(FALSE);
		t3lib_div::setSingletonInstance('t3lib_diff', $differenceService);

		// Add sorting field to list of fields to be processed:
		t3lib_div::loadTCA($table);
		$sortingField = $this->getFieldDeviationService()->getTcaControlField($table, 'sortby');
		if ($sortingField !== NULL && !empty($GLOBALS['TCA'][$table]['interface']['showRecordFieldList'])) {
			$processFields = t3lib_div::trimExplode(',', $GLOBALS['TCA'][$table]['interface']['showRecordFieldList'], TRUE);
			if (!in_array($sortingField, $processFields)) {
				$processFields[] = $sortingField;
				$GLOBALS['TCA'][$table]['interface']['showRecordFieldList'] = implode(',', $processFields);
			}
			if (empty($GLOBALS['TCA'][$table]['columns'][$sortingField])) {
				$GLOBALS['TCA'][$table]['columns'][$sortingField] = array(
					'label' => 'LLL:EXT:lang/locallang_core.xml:show_item.php.sorting',
					'config' => array(
						'type' => 'input',
						'eval' => 'int',
					),
				);
				$isTcaModified = TRUE;
			}
		}

		$result = parent::getRowDetails($parameter);

		if ($isTcaModified) {
			unset($GLOBALS['TCA'][$table]['columns'][$sortingField]);
		}

		return $result;
	}

	/**
	 * Gets affected elements on publishing/swapping actions.
	 * Affected elements have a dependency, e.g. translation overlay
	 * and the default origin record - thus, the default record would be
	 * affected if the translation overlay shall be published.
	 *
	 * @param stdClass $parameters
	 * @return array
	 */
	protected function getAffectedElements(stdClass $parameters) {
		if (isset($parameters->stage) && t3lib_div::testInt($parameters->stage)) {
			$this->getWorkspaceService()->setStage($parameters->stage);
		}

		$affectedElements = parent::getAffectedElements($parameters);
		$this->getWorkspaceService()->reset();

		return $affectedElements;
	}

	/**
	 * @return Tx_IrreWorkspaces_Service_Field_DeviationService
	 */
	protected function getFieldDeviationService() {
		return t3lib_div::makeInstance('Tx_IrreWorkspaces_Service_Field_DeviationService');
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