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
class Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode extends Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode implements Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface {
	/**
	 * @var string
	 */
	protected $comment;

	/**
	 * @var Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface
	 */
	protected $parent;

	/**
	 * @var string
	 */
	protected $startTag;

	/**
	 * @var string
	 */
	protected $endTag;

	/**
	 * @var string
	 */
	protected $tagName;

	/**
	 * @param string $startTag
	 * @param string $endTag
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode
	 */
	static public function create($startTag, $endTag = '') {
		return t3lib_div::makeInstance(
			'Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode',
			$startTag,
			$endTag
		);
	}

	public function __construct($startTag, $endTag = '') {
		parent::__construct();
		$this->setStartTag($startTag);
		$this->setEndTag($endTag);
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

	/**
	 * @return void
	 */
	public function moveChildrenToParent() {
		/** @var $parentNode Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode */
		$parentNode = $this->getParent();
		$parentNode->moveChildren($this->children, $this);
	}

	/**
	 * @param string $startTag
	 */
	public function setStartTag($startTag) {
		$this->startTag = (string) $startTag;

		$matches = array();
		if (preg_match('#^<\s*([^>\s]+)#', $this->startTag, $matches)) {
			$this->tagName = $matches[1];
		}
	}

	/**
	 * @return string
	 */
	public function getStartTag() {
		return $this->startTag;
	}

	/**
	 * @return string
	 */
	public function getTagName() {
		return $this->tagName;
	}

	/**
	 * @param string $endTag
	 */
	public function setEndTag($endTag) {
		$this->endTag = (string) $endTag;
	}

	/**
	 * @return string
	 */
	public function getEndTag() {
		return $this->endTag;
	}
}

?>