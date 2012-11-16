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
abstract class Tx_IrreWorkspaces_Domain_Model_Node_AbstractContentNode implements Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface {
	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface
	 */
	protected $parent;

	/**
	 * @param string $content
	 */
	public function __construct($content) {
		$this->setContent($content);
	}

	public function __clone() {
		$this->parent = NULL;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = (string) $content;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface $node
	 */
	public function setParent(Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface $node) {
		$this->parent = $node;
	}

	/**
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_RootNode|NULL
	 */
	public function getRoot() {
		$currentNode = $this;

		while ($parentNode = $currentNode->getParent()) {
			if ($parentNode instanceof Tx_IrreWorkspaces_Domain_Model_Node_RootNode) {
				return $parentNode;
			}
			$currentNode = $parentNode;
		}

		return NULL;
	}
}

?>