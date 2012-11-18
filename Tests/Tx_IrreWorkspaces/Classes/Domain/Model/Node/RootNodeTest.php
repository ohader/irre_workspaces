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
class Tx_IrreWorkspaces_Domain_Model_Node_RootNodeTest extends Tx_Phpunit_TestCase {
	/**
	 * @test
	 * @expectedException LogicException
	 */
	public function sameHtmlNodeCannotBeAddedToDifferentParents() {
		$firstRootNode = Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create();
		$secondRootNode = Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create();

		$htmlNode = Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create('<p>', '</p>')
			->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create('Text'));

		$firstRootNode->addChild($htmlNode);
		$secondRootNode->addChild($htmlNode);
	}

	/**
	 * @test
	 */
	public function clonedHtmlNodeCannotBeAddedToDifferentParents() {
		$firstRootNode = Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create();
		$secondRootNode = Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create();

		$htmlNode = Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create('<p>', '</p>');
		$htmlNode->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create('Text'));

		$firstRootNode->addChild($htmlNode);
		$secondRootNode->addChild(clone $htmlNode);
		$htmlNode->setStartTag('<p class="first">');

		$count = 0;

		foreach ($secondRootNode->findHtmlNodes() as $clonedHtmlNode) {
			$this->assertNotEquals($htmlNode, $clonedHtmlNode);
			$this->assertEquals('<p>', $clonedHtmlNode->getStartTag());
			$this->assertEquals($secondRootNode, $clonedHtmlNode->getParent());

			foreach ($clonedHtmlNode->findTextNodes() as $textNode) {
				$this->assertEquals(1, ++$count);
				$this->assertEquals('Text', $textNode->getContent());
				$this->assertEquals($clonedHtmlNode, $textNode->getParent());
			}
		}
	}
}

?>