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
class Tx_IrreWorkspaces_Renderer_Notification_MessageRenderer {
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
		return $this->substituteMarkers($content);
	}

	/**
	 * @param string $key
	 * @param array|object $value
	 * @return Tx_IrreWorkspaces_Renderer_Notification_MessageRenderer
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
	 * @return string
	 */
	protected function substituteMarkers($content) {
		$matches = array();
		$search = array();
		$replace = array();

			// Iterator pattern:
			// Example:
			// ###iterator:object.property(property)###
			// ###iterator.property.title###
			// ###/iterator:object.property###
		if (preg_match_all('!###iterator:([^#(]+)\(([^)]+)\)###\n?(.+)\n?###/iterator:\1###\n?!mis', $content, $matches)) {
			$variables = $this->variables;

			foreach ($matches[0] as $index => $rawData) {
				$path = $matches[1][$index];
				$name = $matches[2][$index];
				$inner = $matches[3][$index];
				$iterator = $this->resolveVariable($path, FALSE);

				$result = '';

				if (is_array($iterator) || $iterator instanceof Traversable) {
					foreach ($iterator as $value) {
						$iteratorVariables = array_merge(
							(array) $this->variables['iterator'],
							array($name => $value)
						);

						$this->assign('iterator', $iteratorVariables);
						$result .= $this->substituteMarkers($inner);
					}
				}

				$search[] = $rawData;
				$replace[] = $result;
			}

			$this->variables = $variables;
		}

			// Regular string elements:
		if (preg_match_all('/###([^#]+)###/', $content, $matches)) {
			foreach ($matches[1] as $index => $path) {
				$resolvedValue = $this->resolveVariable($path);

				if ($this->replaceUnknownMarkers || $resolvedValue !== NULL) {
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
		$segments = t3lib_div::trimExplode('.', strtolower($path));
		$lastSegment = $segments[count($segments) - 1];

		foreach ($segments as $index => $segment) {
			if ($index === 0 && isset($this->variables[$segment])) {
				$data = $this->variables[$segment];
			} elseif (is_object($data) && method_exists($data, 'get' . ucfirst($segment))) {
				$data = call_user_func_array(
					array($data, 'get' . ucfirst($segment)),
					array()
				);
			} elseif (is_array($data) && isset($data[$segment])) {
				$data = $data[$segment];
			} else {
				$data = NULL;
				$toString = FALSE;
				break;
			}
		}

		if ($data instanceof DateTime) {
			/** @var $dateTime DateTime */
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
}

?>