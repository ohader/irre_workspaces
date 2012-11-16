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
class Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection {
	/**
	 * @var array|Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface[]
	 */
	protected $nodes;

	/**
	 * @param array $nodes
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection
	 */
	static public function create(array $nodes = array()) {
		return t3lib_div::makeInstance(
			'Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection',
			$nodes
		);
	}

	/**
	 * @param array $nodes
	 */
	public function __construct(array $nodes = array()) {
		$this->nodes = array();

		foreach ($nodes as $node) {
			$this->add($node);
		}
	}

	/**
	 * @return array|Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface[]
	 */
	public function __toArray() {
		return $this->nodes;
	}

	/**
	 * @return integer
	 */
	public function count() {
		return count($this->nodes);
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node
	 * @return boolean
	 */
	public function has(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		return ($this->getIndexOf($node) !== FALSE);
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode
	 */
	public function add(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		$this->nodes[] = $node;
	}

	public function remove(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		if (FALSE !== $index = $this->getIndexOf($node)) {
			unset($this->nodes[$index]);
		}
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection $collection
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $afterNode
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_AbstractChildrenNode
	 */
	public function merge(Tx_IrreWorkspaces_Domain_Model_Node_NodeCollection $collection, Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $afterNode = NULL) {
		if ($afterNode === NULL) {
			$this->nodes = array_merge($this->nodes, $collection->__toArray());
		} elseif (FALSE !== $index = $this->getIndexOf($afterNode)) {
			array_splice(
				$this->nodes,
				$index,
				1,
				array_merge(
					array($afterNode),
					$collection->__toArray()
				)
			);
		}
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node
	 * @return boolean|integer
	 */
	public function getIndexOf(Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface $node) {
		return array_search($node, $this->nodes, TRUE);
	}

	/**
	 * @param string $className
	 * @return array|Tx_IrreWorkspaces_Domain_Model_Node_HasParentInterface[]
	 */
	public function findByClassName($className) {
		$nodes = array();

		foreach ($this->nodes as $node) {
			if (is_a($node, $className)) {
				$nodes[] = $node;
			}
		}

		return $nodes;
	}
}

?>