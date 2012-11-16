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
abstract class Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode implements Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface {
	/**
	 * @var Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection
	 */
	protected $children;

	public function __construct() {
		$this->children = Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection::create();
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node
	 * @return boolean
	 */
	public function hasChild(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		return $this->children->has($node);
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return ($this->children->count() > 0);
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode
	 * @throws LogicException
	 */
	public function addChild(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		if ($node->getParent() !== NULL && $node->getParent() !== $this) {
			throw new LogicException('Node is already child of another parent');
		}

		$node->setParent($this);
		$this->children->add($node);
		return $this;
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection $collection
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $afterNode
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode
	 */
	public function moveChildren(Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection $collection, Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $afterNode = NULL) {
		$this->children->merge($collection, $afterNode);

		foreach ($collection->__toArray() as $node) {
			$collection->remove($node);
			$node->setParent($this);
		}

		return $this;
	}

	public function purgeChildren() {
		$this->children = Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection::create();
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node
	 * @return boolean|integer
	 */
	public function getIndexOfChild(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		return $this->children->getIndexOf($node);
	}

	/**
	 * @return array|Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode[]
	 */
	public function findHtmlNodes() {
		return $this->children->findByClassName('Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode');
	}

	/**
	 * @return array|Tx_IrreWorkspaces_Domain_Model_Node_TextNode[]
	 */
	public function findTextNodes() {
		return $this->children->findByClassName('Tx_IrreWorkspaces_Domain_Model_Node_TextNode');
	}
}

?>