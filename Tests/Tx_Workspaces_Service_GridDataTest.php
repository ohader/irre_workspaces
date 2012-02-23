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
class Tx_Workspaces_Service_GridDataTest extends Tx_Phpunit_TestCase {
	/**
	 * @var integer
	 */
	protected $collectionValue;

	/**
	 * @var Tx_Workspaces_Service_GridData
	 */
	protected $gridData;

	/**
	 * @var Ux_Tx_Workspaces_Service_GridData|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $gridDataMock;

	/**
	 * @var string
	 */
	protected $parentTableName;

	/**
	 * @var string
	 */
	protected $childTableName;

	/**
	 * @var array
	 */
	protected $records;

	/**
	 * Sets up this test case
	 */
	protected function setUp() {
		$this->collectionValue = rand(100, 200);
		$this->gridData = t3lib_div::makeInstance('Tx_Workspaces_Service_GridData');
		$this->setUpTca();
		$this->setUpRecords();
	}

	/**
	 * Tears down this test case
	 */
	protected function tearDown() {
		unset($this->collectionValue);
		unset($this->gridData);
		unset($this->gridDataMock);
		unset($this->records);
		$this->tearDownTca();
	}

	/**
	 * @test
	 */
	public function isXClassUsed() {
		$this->assertInstanceOf('Ux_Tx_Workspaces_Service_GridData', $this->gridData);
	}

	/**
	 * @test
	 */
	public function isColumnSetForCompleteDataArray() {
		$this->gridDataMock = $this->getMock(
			'Ux_Tx_Workspaces_Service_GridData',
			array('getDataArrayFromCache')
		);
		// Do not query database:
		$this->gridDataMock->expects($this->once())
			->method('getDataArrayFromCache')
			->will($this->returnCallback(array($this, 'getDataArrayFromCacheCallback')));

		$parameters = new stdClass();
		$result = $this->gridDataMock->generateGridListFromVersions(array(), $parameters, 1);

		foreach ($result['data'] as $data) {
			$this->assertTrue(
				isset($data[Ux_Tx_Workspaces_Service_GridData::GridColumn_Collection])
			);
		}
	}

	/**
	 * @test
	 */
	public function areRegularRecordsLimited() {
		$this->gridDataMock = $this->getMock(
			'Ux_Tx_Workspaces_Service_GridData',
			array('getDataArrayFromCache', 'setCollectionIdentifier')
		);
		// Do not query database:
		$this->gridDataMock->expects($this->once())
			->method('getDataArrayFromCache')
			->will($this->returnCallback(array($this, 'getDataArrayFromCacheCallback')));

		$parameters = new stdClass();
		$parameters->limit = 3;

		$result = $this->gridDataMock->generateGridListFromVersions(array(), $parameters, 1);

		$this->assertEquals(
			$parameters->limit,
			count($result['data'])
		);
	}

	/**
	 * @test
	 */
	public function areDependentRecordsCarriedOverLimit() {
		$this->gridDataMock = $this->getMock(
			'Ux_Tx_Workspaces_Service_GridData',
			array('getDataArrayFromCache', 'setCollectionIdentifier')
		);
		// Do not query database:
		$this->gridDataMock->expects($this->once())
			->method('getDataArrayFromCache')
			->will($this->returnCallback(array($this, 'getDataArrayFromCacheCallback')));
		// Set special collection value for all:
		$this->gridDataMock->expects($this->any())
			->method('setCollectionIdentifier')
			->will($this->returnCallback(array($this, 'setCollectionIdentifierCallback')));

		$parameters = new stdClass();
		$parameters->limit = 3;

		$result = $this->gridDataMock->generateGridListFromVersions(array(), $parameters, 1);

		$this->assertEquals(
			count($this->records),
			count($result['data'])
		);
	}

	/**
	 * Overrides getDataArrayFromCache()
	 */
	public function getDataArrayFromCacheCallback() {
		$this->gridDataMock->setDataArray($this->records);
	}

	/**
	 * Overrides setCollectionIdentifier()
	 *
	 * @param array $element
	 * @param integer $value
	 */
	public function setCollectionIdentifierCallback(array &$element, $value) {
		$element[Ux_Tx_Workspaces_Service_GridData::GridColumn_Collection] = $value ?: $this->collectionValue;
	}

	/**
	 * Sets up test TCA
	 */
	protected function setUpTca() {
		$this->parentTableName = uniqid('tx_parent');
		$this->childTableName = uniqid('tx_child');

		$GLOBALS['TCA'][$this->parentTableName] = array(
			'columns' => array(
				'children' => array(
					'config' => array(
						'type' => 'inline',
						'foreign_table' => $this->childTableName,
						'foreign_table_field' => 'parentid',
					),
				),
			),
		);

		$GLOBALS['TCA'][$this->childTableName] = array(
			'columns' => array(
				'parentid' => array(
					'config' => array(
						'type' => 'passthrough',
					),
				)
			),
		);
	}

	/**
	 * Tears down test TCA
	 */
	protected function tearDownTca() {
		unset($GLOBALS['TCA'][$this->parentTableName]);
		unset($GLOBALS['TCA'][$this->childTableName]);

		unset($this->parentTableName);
		unset($this->childTableName);
	}

	/**
	 * Sets up virtual test records.
	 *
	 * @param integer $children
	 */
	protected function setUpRecords($children = 10) {
		$this->records = array();

		$this->records[] = array(
			'table' => $this->parentTableName,
			'pid' => 1,
			'uid' => 1,
		);

		for ($i=1; $i <= $children; $i++) {
			$this->records[] = array(
				'table' => $this->childTableName,
				'pid' => 1,
				'uid' => $i,
			);
		}
	}
}

?>