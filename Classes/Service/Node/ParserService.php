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
class Tx_IrreWorkspaces_Service_Node_ParserService implements t3lib_Singleton {
	/**
	 * @param string $html
	 * @return Tx_IrreWorkspaces_Domain_Model_Node_RootNode
	 */
	public function execute($html) {
		$rootNode = Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create();

		$this->parse(
			$this->getIterator(
				$this->preParse($html)
			),
			$rootNode
		);

		$this->postProcess($rootNode);

		return $rootNode;
	}

	/**
	 * @param array $preParsedParts
	 * @return ArrayIterator
	 */
	protected function getIterator(array $preParsedParts) {
		$arrayObject = new ArrayObject($preParsedParts);
		return $arrayObject->getIterator();
	}

	/**
	 * @param string $html
	 * @return array
	 */
	protected function preParse($html) {
		$results = array();
		$outerParts = explode('<', $html, 2);

		if ($outerParts[0] !== '') {
			$results[] = $outerParts[0];
		}

		if (isset($outerParts[1])) {
			$innerParts = explode('>', $outerParts[1], 2);

			// Valid part, so add it a recurse
			if (isset($innerParts[1])) {
				$results[] = '<' . $innerParts[0] . '>';
				$results = array_merge($results, $this->preParse($innerParts[1]));
			// Faulty part, so escape the opening tag
			} else {
				$results[] = htmlspecialchars('<') . $innerParts[0];
			}
		}

		return $results;
	}

	protected function parse(ArrayIterator $iterator, Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface $currentNode) {
		while ($iterator->valid()) {
			$matches = array();
			$part = $iterator->current();

			// Text
			if (strpos($part, '<') === FALSE) {
				$iterator->next();
				$currentNode->addChild(
					Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create($part)
				);
			// Ending tag part
			} elseif (preg_match('#^<\s*/([^>\s]+)#', $part, $matches)) {
				$iterator->next();
				$endTagName = $matches[1];
				if ($matchingNode = $this->findNodeWithTagName($endTagName, $currentNode)) {
					$matchingNode->setEndTag($part);
					$currentNode = $matchingNode->getParent();
				}
			// Comment
			} elseif (preg_match('#^<\s*!--#', $part)) {
				$iterator->next();
				$currentNode->addChild(
					Tx_IrreWorkspaces_Domain_Model_Node_CommentNode::create($part)
				);
			// First tag part
			} else {
				$iterator->next();
				$htmlNode = Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create($part);
				$currentNode->addChild($htmlNode);
				$this->parse($iterator, $htmlNode);
			}
		}
	}

	protected function postProcess(Tx_IrreWorkspaces_Domain_Model_Node_HasChildrenInterface $currentNode) {
		foreach ($currentNode->findHtmlNodes() as $htmlNode) {
			if ($htmlNode->hasChildren()) {
				$this->postProcess($htmlNode);
			}

			// Move collection branch to parent level
			if ($htmlNode->getEndTag() === '') {
				$htmlNode->moveChildrenToParent();
			}
		}
	}

	/**
	 * @param string $tagName
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode $currentNode
	 * @return NULL|Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode
	 */
	protected function findNodeWithTagName($tagName, Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode $currentNode) {
		/** @var $parentNode Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode */
		while ($currentNode instanceof Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode) {
			if ($currentNode->getTagName() === $tagName) {
				return $currentNode;
			}
			$currentNode = $parentNode->getParent();
		}

		return NULL;
	}

}

?>