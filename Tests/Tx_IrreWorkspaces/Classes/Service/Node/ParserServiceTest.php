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
class Tx_IrreWorkspaces_Service_Node_ParserServiceTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_IrreWorkspaces_Service_Node_ParserService
	 */
	protected $parserService;

	/**
	 * Sets up each test case.
	 */
	protected function setUp() {
		$this->parserService = new Tx_IrreWorkspaces_Service_Node_ParserService();
	}

	/**
	 * Tears down each test case.
	 */
	protected function tearDown() {
		unset($this->parserService);
	}

	/**
	 * @param Tx_IrreWorkspaces_Domain_Model_Node_RootNode $expected
	 * @param string $html
	 * @dataProvider elementsAreParsedIntoElementCollectionDataProvider
	 * @test
	 */
	public function elementsAreParsedIntoElementCollection(Tx_IrreWorkspaces_Domain_Model_Node_RootNode $expected = NULL, $html) {
		$result = $this->parserService->execute($html);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function elementsAreParsedIntoElementCollectionDataProvider() {
		return array(
			'plain text elements' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create('Lorem ipsum. Whatever we could expect here.')),
				'Lorem ipsum. Whatever we could expect here.',
			),
			'plain HTML elements #1' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create('<img src="workspaces.png" class="add-border" data-test="test" />')),
				'<img src="workspaces.png" class="add-border" data-test="test" />',
			),
			'plain HTML elements #2' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create('<img src="workspaces.png" class="add-border" data-test="test">')),
				'<img src="workspaces.png" class="add-border" data-test="test">',
			),
			'comment elements' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_CommentNode::create('<!-- This is a comment -->')),
				'<!-- This is a comment -->',
			),
			'mixed elements #1' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create(
					'<a href="http://google.com/" class="link">', '</a>')
					->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create('This is a link to Google'))
				),
				'<a href="http://google.com/" class="link">This is a link to Google</a>',
			),
			'mixed elements #2' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create('Before this '))
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create(
					'<a href="#anchor">', '</a>')
					->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create('anchor'))
				)
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(' we can find some text.')),
				'Before this <a href="#anchor">anchor</a> we can find some text.',
			),
			'mixed elements #3' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(
					'This image ')
				)
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create(
					'<img src="white.png" alt="something white" />')
				)
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(
					' obviously does not show much.')
				),
				'This image <img src="white.png" alt="something white" /> obviously does not show much.',
			),
			'nested elements' => array(
				Tx_IrreWorkspaces_Domain_Model_Node_RootNode::create()
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(
					'Before we ')
				)
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create(
					'<div class="block">', '</div>')
					->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(
						'start to think about ')
					)
					->addChild(Tx_IrreWorkspaces_Domain_Model_Node_HtmlNode::create(
						'<p class="bodytext">', '</p>')
						->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(
							'paragraphs')
						)
					)
				)
				->addChild(Tx_IrreWorkspaces_Domain_Model_Node_TextNode::create(
					' we need to test.')
				),
				'Before we <div class="block">start to think about <p class="bodytext">paragraphs</p></div> we need to test.',
			),
		);
	}
}

?>