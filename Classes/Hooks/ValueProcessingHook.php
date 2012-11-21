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
class Tx_IrreWorkspaces_Hooks_ValueProcessingHook implements t3lib_Singleton {
	/**
	 * @var array
	 */
	protected $valueStack = array();

	/**
	 * Pre-processes value rendering.
	 * This method is required to keep the initial value to be processed.
	 *
	 *
	 * @param array $configuration
	 * @return void
	 * @see t3lib_BEfunc::getProcessedValue
	 */
	public function preProcess(array $configuration) {
		if ($this->isFlexFormFieldConfiguration($configuration)) {
			$originalParameters = $this->getGetProcessedValueParameters();
			$this->valueStack[] = $originalParameters['value'];
		}
	}

	/**
	 * Post-processes value rendering.
	 * Since the value has been processed by TYPO3 already, it's required
	 * to use the previously fetched value from the preProcess() method.
	 *
	 * @param array $parameters
	 * @return string
	 * @throws RuntimeException
	 * @see t3lib_BEfunc::getProcessedValue
	 */
	public function postProcess(array $parameters) {
		$value = $parameters['value'];
		$configuration = $parameters['colConf'];

		if ($this->isFlexFormFieldConfiguration($configuration)) {
			if (empty($this->valueStack)) {
				throw new RuntimeException('Post-processing was triggered without any pre-processing', 1352712620);
			}

			$originalParameters = $this->getGetProcessedValueParameters();

			$valueFromStack = array_pop($this->valueStack);
			$valueStructure = t3lib_div::xml2array($valueFromStack);

			if ($this->canPostProcessFlexForm($configuration, $originalParameters['uid']) && !empty($valueStructure['data']) && is_array($valueStructure['data'])) {
				$dataStructure = t3lib_BEfunc::getFlexFormDS(
					$configuration,
					$this->getRecord($originalParameters),
					$originalParameters['table'],
					'',
					TRUE
				);

				$valueStructure = t3lib_div::xml2array($valueFromStack);
				$processedSheets = $this->getProcessedSheets($dataStructure, $valueStructure['data']);
				$value = trim($this->renderFlexFormValuePlain($processedSheets));
			}
		}

		return $value;
	}

	/**
	 * @param array $processedData
	 * @return string
	 * @deprecated Currently not used due to a hardcoded htmlspecialchars() in t3lib_diff
	 */
	protected function renderFlexFormValueHtml(array $processedData) {
		$value = '';

		foreach ($processedData as $processedKey => $processedValue) {
			$dataAttribute = 'data-processed-key="' . htmlspecialchars($processedKey) . '"';

			if (!empty($processedValue['children'])) {
				$children = $this->renderFlexFormValueHtml($processedValue['children']);

				if (empty($processedValue['section'])) {
					$value .= '<li ' . $dataAttribute . '>' . $processedValue['title'] . ': ' . $children . '</li>' . PHP_EOL;
				} elseif ($children) {
					$value .= '<li ' . $dataAttribute . '>' . $children . '</li>' . PHP_EOL;
				}
			} elseif (isset($processedValue['value'])) {
				$value .= '<li ' . $dataAttribute . '>' . $processedValue['title'] . ': ' . $processedValue['value'] . '</li>' . PHP_EOL;
			}
		}

		if ($value) {
			$value = '<ul>' . $value . '</ul>';
		}

		return $value;
	}

	/**
	 * @param array $processedData
	 * @param string $titlePrefix
	 * @return string
	 */
	protected function renderFlexFormValuePlain(array $processedData, $titlePrefix = '') {
		$value = '';

		foreach ($processedData as $processedKey => $processedValue) {
			$title = $titlePrefix . (!empty($processedValue['section']) ? $processedKey : $processedValue['title']);

			if (!empty($processedValue['children'])) {
				$children = $this->renderFlexFormValuePlain($processedValue['children'], $title . '/');

				if (empty($processedValue['section']) && empty($processedValue['container'])) {
					$value .= $title . ':' . PHP_EOL . $children . PHP_EOL . PHP_EOL;
				} elseif ($children) {
					$value .= $children . PHP_EOL . PHP_EOL;
				}
			} elseif (isset($processedValue['value'])) {
				$value .= $title . ':' . PHP_EOL . $processedValue['value'] . PHP_EOL . PHP_EOL;
			}
		}

		return $value;
	}

	/**
	 * Gets processed sheets.
	 *
	 * @param array $dataStructure
	 * @param array $valueStructure
	 * @return array
	 */
	protected function getProcessedSheets(array $dataStructure, array $valueStructure) {
		$processedSheets = array();

		foreach ($dataStructure['sheets'] as $sheetKey => $sheetStructure) {
			if (!empty($sheetStructure['ROOT']['el'])) {
				$sheetTitle = $sheetKey;
				if (!empty($sheetStructure['ROOT']['TCEforms']['sheetTitle'])) {
					$sheetTitle = $this->getLanguageObject()->sL($sheetStructure['ROOT']['TCEforms']['sheetTitle']);
				}

				if (!empty($valueStructure[$sheetKey]['lDEF'])) {
					$processedElements = $this->getProcessedElements(
						$sheetStructure['ROOT']['el'],
						$valueStructure[$sheetKey]['lDEF']
					);
					$processedSheets[$sheetKey] = array(
						'title' => $sheetTitle,
						'children' => $processedElements,
					);
				}
			}
		}

		return $processedSheets;
	}

	/**
	 * Gets processed elements.
	 *
	 * @param array $dataStructure
	 * @param array $valueStructure
	 * @return array
	 */
	protected function getProcessedElements(array $dataStructure, array $valueStructure) {
		$processedElements = array();

		// Values used to fake TCA
		$processingTableValue = uniqid('processing');
		$processingColumnValue = uniqid('processing');

		foreach ($dataStructure as $elementKey => $elementStructure) {
			$elementTitle = $this->getElementTitle($elementKey, $elementStructure);

			// Render section or container
			if (!empty($elementStructure['type']) && $elementStructure['type'] === 'array') {
				if (empty($valueStructure[$elementKey]['el'])) {
					continue;
				}

				// Render section
				if (!empty($elementStructure['section'])) {
					$processedElements[$elementKey] = array(
						'section' => TRUE,
						'title' => $elementTitle,
						'children' => $this->getProcessedSections(
							$elementStructure['el'],
							$valueStructure[$elementKey]['el']
						),
					);
				// Render container
				} else {
					$processedElements[$elementKey] = array(
						'container' => TRUE,
						'title' => $elementTitle,
						'children' => $this->getProcessedElements(
							$elementStructure['el'],
							$valueStructure[$elementKey]['el']
						),
					);
				}

			// Render plain elements
			} elseif (!empty($elementStructure['TCEforms']['config'])) {
				$GLOBALS['TCA'][$processingTableValue]['columns'][$processingColumnValue]['config'] = $elementStructure['TCEforms']['config'];

				$processedElements[$elementKey] = array(
					'title' => $elementTitle,
					'value' => t3lib_BEfunc::getProcessedValue(
						$processingTableValue,
						$processingColumnValue,
						$valueStructure[$elementKey]['vDEF'],
						0,
						FALSE
					),
				);
			}
		}

		if (!empty($GLOBALS['TCA'][$processingTableValue])) {
			unset($GLOBALS['TCA'][$processingTableValue]);
		}

		return $processedElements;
	}

	/**
	 * Gets processed sections.
	 *
	 * @param array $dataStructure
	 * @param array $valueStructure
	 * @return array
	 */
	protected function getProcessedSections(array $dataStructure, array $valueStructure) {
		$processedSections = array();

		foreach ($valueStructure as $sectionValueIndex => $sectionValueStructure) {
			$processedSections[$sectionValueIndex] = array(
				'section' => TRUE,
				'children' => $this->getProcessedElements(
					$dataStructure,
					$sectionValueStructure
				),
			);
		}

		return $processedSections;
	}

	/**
	 * @param string $key
	 * @param array $structure
	 * @return string
	 */
	protected function getElementTitle($key, array $structure) {
		$title = $key;

		if (!empty($structure['TCEforms']['label'])) {
			$title = $this->getLanguageObject()->sL($structure['TCEforms']['label']);
		} elseif (!empty($structure['tx_templavoila']['title'])) {
			$title = $this->getLanguageObject()->sL($structure['tx_templavoila']['title']);
		}


		return $title;
	}

	/**
	 * @param array $configuration
	 * @return boolean
	 */
	protected function isFlexFormFieldConfiguration(array $configuration) {
		return (!empty($configuration['type']) && $configuration['type'] === 'flex');
	}

	/**
	 * @param array $configuration
	 * @param integer $uid
	 * @return boolean
	 */
	protected function canPostProcessFlexForm(array $configuration, $uid) {
		return (
			empty($configuration['ds_pointerField'])
			|| is_array($configuration['ds']) && count($configuration['ds']) === 1
			|| $uid > 0
		);
	}

	/**
	 * @param array $parameters
	 * @return array
	 */
	protected function getRecord(array $parameters) {
		return t3lib_BEfunc::getRecord(
			$parameters['table'],
			$parameters['uid']
		);
	}

	/**
	 * @return array|NULL
	 */
	protected function getGetProcessedValueParameters() {
		$arguments = NULL;
		$stack = debug_backtrace();

		foreach ($stack as $stackItem) {
			if (isset($stackItem['class']) && $stackItem['class'] === 't3lib_BEfunc' && $stackItem['function'] === 'getProcessedValue') {
				$arguments = array(
					'table' => $stackItem['args'][0],
					'col' => $stackItem['args'][1],
					'value' => $stackItem['args'][2],
					'uid' => $stackItem['args'][6],
				);
				break;
			}
		}

		return $arguments;
	}

	/**
	 * Determines whether a field is considered to be internal.
	 *
	 * @param string $table Name of the table
	 * @param string $field Name of the field to be checked
	 * @return boolean
	 */
	protected function isInternalField($table, $field) {
		$result = FALSE;

			// Regular system fields:
		if (t3lib_div::inList('uid,pid', $field)) {
			$result = TRUE;

			// Translation differences:
		} elseif (!empty($GLOBALS['TCA'][$table]['ctrl']['transOrigDiffSourceField']) && $field === $GLOBALS['TCA'][$table]['ctrl']['transOrigDiffSourceField']) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * @return language
	 */
	protected function getLanguageObject() {
		return $GLOBALS['LANG'];
	}
}

?>