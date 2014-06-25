<?php
namespace OliverHader\IrreWorkspaces\Service;

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
class MessageRenderingService {
	/**
	 * @var array
	 */
	protected $variables = array();

	/**
	 * @var boolean
	 */
	protected $replaceUnknownMarkers = TRUE;

	/**
	 * @param string $content
	 * @return string
	 */
	public function render($content) {
		$content = $this->substituteMarkers($content, FALSE);

		if ($this->replaceUnknownMarkers) {
			$content = $this->substituteMarkers($content, TRUE);
		}

		return $content;
	}

	/**
	 * @param string $key
	 * @param array|object $value
	 * @return MessageRenderingService
	 */
	public function assign($key, $value) {
		$key = strtolower($key);
		$this->variables[$key] = $value;
		return $this;
	}

	/**
	 * @param boolean $replaceUnknownMarkers
	 */
	public function setReplaceUnknownMarkers($replaceUnknownMarkers) {
		$this->replaceUnknownMarkers = (bool) $replaceUnknownMarkers;
	}

	/**
	 * Substitutes markes by accordant Fluid-styled content lookups.
	 *
	 * @param string $content
	 * @param boolean $replaceUnknownMarkers
	 * @return string
	 */
	protected function substituteMarkers($content, $replaceUnknownMarkers = FALSE) {
		$matches = array();

			// Iterator pattern:
			// Example:
			// ###iterator:object.property(property)###
			// ###iterator.property.title###
			// ###/iterator:object.property###
		if (preg_match_all('!###iterator:([^#(]+)\(([^)]+)\)###\n?(.+)\n?###/iterator:\1###\n?!mis', $content, $matches)) {
			$variables = $this->variables;
			$iteratorSearch = array();
			$iteratorReplace = array();

			foreach ($matches[0] as $index => $rawData) {
				$path = $matches[1][$index];
				$name = $matches[2][$index];
				$inner = $matches[3][$index];
				$iterator = $this->resolveVariable($path, FALSE);

				$result = '';

				if (is_array($iterator) || $iterator instanceof \Traversable) {
					foreach ($iterator as $value) {
						$iteratorVariables = array_merge(
							(array) $this->variables['iterator'],
							array($name => $value)
						);

						$this->assign('iterator', $iteratorVariables);
						$result .= $this->substituteMarkers($inner);
					}
				}

				$iteratorSearch[] = $rawData;
				$iteratorReplace[] = $result;
			}

			$content = str_replace(
				$iteratorSearch,
				$iteratorReplace,
				$content
			);

			$this->variables = $variables;
		}

			// Regular string elements:
		if (preg_match_all('/###([^#]+)###/', $content, $matches)) {
			$search = array();
			$replace = array();

			foreach ($matches[1] as $index => $path) {
				$resolvedValue = $this->resolveVariable($path);

				if ($replaceUnknownMarkers || $resolvedValue !== NULL) {
					$search[] = $matches[0][$index];
					$replace[] = $resolvedValue;
				}
			}

			$content = str_replace(
				$search,
				$replace,
				$content
			);
		}

		return $content;
	}

	/**
	 * @param string $path
	 * @param boolean $toString
	 * @return mixed|NULL|string
	 */
	protected function resolveVariable($path, $toString = TRUE) {
		$data = NULL;
		$segments = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('.', $path);
		$lastSegment = $segments[count($segments) - 1];

		foreach ($segments as $index => $segment) {
			if ($index === 0 && NULL !== $possibleKey = $this->getPossibleArrayKey($this->variables, $segment)) {
				$data = $this->variables[$possibleKey];
			} elseif (is_object($data) && NULL !== $possibleMethod = $this->getPossibleObjectMethod($data, $segment)) {
				$data = call_user_func_array(
					array($data, $possibleMethod),
					array()
				);
			} elseif (is_array($data) && NULL !== $possibleKey = $this->getPossibleArrayKey($data, $segment)) {
				$data = $data[$possibleKey];
			} else {
				$data = NULL;
				$toString = FALSE;
				break;
			}
		}

		if ($data instanceof \DateTime) {
			/** @var $dateTime \DateTime */
			$dateTime = $data;
			if (strpos($lastSegment, 'time') !== FALSE) {
				$data = $dateTime->format('H:i');
			} else {
				$data = $dateTime->format('d.m.Y');
			}
		}

		if ($toString) {
			$data = (string) $data;
		}

		return $data;
	}

	protected function getPossibleArrayKey(array $array, $key) {
		$possibleKey = NULL;
		$lowerKey = strtolower($key);

		if (isset($array[$key])) {
			$possibleKey = $key;
		} elseif (isset($array[$lowerKey])) {
			$possibleKey = $lowerKey;
		}

		return $possibleKey;
	}

	protected function getPossibleObjectMethod($object, $key) {
		$possibleMethod = NULL;
		$lowerKey = strtolower($key);

		if (method_exists($object, 'get' . ucfirst($key))) {
			$possibleMethod = 'get' . ucfirst($key);
		} elseif (method_exists($object, 'get' . ucfirst($lowerKey))) {
			$possibleMethod = 'get' . ucfirst($lowerKey);
		}

		return $possibleMethod;
	}
}

?>