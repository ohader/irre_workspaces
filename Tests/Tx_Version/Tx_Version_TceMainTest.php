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

require_once t3lib_extMgm::extPath('irre_workspaces') . 'Tests/Fixture/TceMainFixture.php';

/**
 * @author Oliver Hader <oliver.hader@typo3.org>
 * @package EXT:irre_workspaces
 */
class Tx_Version_TceMainTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Version_TceMainFixture|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $versionTceMain;

	/**
	 * @var t3lib_TCEmain|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $tceMainMock;

	/**
	 * @var t3lib_DB|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $databaseMock;

	/**
	 * @var Tx_Workspaces_Service_Stages|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $stageServiceMock;

	/**
	 * @var t3lib_DB
	 */
	protected $originalDatabase;

	/**
	 * @var array
	 */
	protected $testRecipients;

	/**
	 * @var array
	 */
	protected $testNotifications;

	/**
	 * @var string
	 */
	protected $testComment;

	/**
	 * @var string
	 */
	protected $testStageTitle;

	/**
	 * @var integer
	 */
	protected $testWorkspaceId;

	/**
	 * @var integer
	 */
	protected $testStageId;

	/**
	 * @var array
	 */
	protected $testRecords;

	/**
	 * @var array|NULL
	 */
	protected $nextRecordCallbackValues;

	/**
	 * Sets up each test case.
	 */
	protected function setUp() {
		$GLOBALS['TCA']['tx_irreworkspaces_test']['ctrl'] = array();

		$this->databaseMock = $this->getMock(
			't3lib_DB',
			array('exec_SELECTquery', 'exec_SELECTgetRows', 'sql_fetch_assoc', 'sql_free_result')
		);
		$this->databaseMock->expects($this->any())->method('exec_SELECTquery')
			->will($this->returnCallback(array($this, 'selectQueryCallback')));
		$this->databaseMock->expects($this->any())->method('exec_SELECTgetRows')
			->will($this->returnCallback(array($this, 'selectQueryRowsCallback')));
		$this->databaseMock->expects($this->any())->method('sql_fetch_assoc')
			->will($this->returnCallback(array($this, 'sqlFetchAssocCallback')));
		$this->databaseMock->expects($this->any())->method('sql_free_result')
			->will($this->returnCallback(array($this, 'sqlFreeResultCallback')));

		$this->originalDatabase = $GLOBALS['TYPO3_DB'];
		$GLOBALS['TYPO3_DB'] = $this->databaseMock;

		$this->testStageTitle = uniqid('stageTitle');
		$this->testComment = uniqid('comment') . PHP_EOL . uniqid('comment');
		$this->testWorkspaceId = 987;
		$this->testStageId = -20;

		$this->testRecipients = array(
			array('email' => 'oliver.hader@typo3.org'),
		);

		$this->testNotifications = array(
			implode(':', array($this->testWorkspaceId, $this->testStageId, $this->testComment)) => array(
				'shared' => array(
					array(
						'uid' => $this->testWorkspaceId,
						'title' => 'Testing Workspace',
						'stagechg_notification' => 1,
					),
					$this->testStageId,
					$this->testComment,
				),
				'elements' => array(
					'tx_irreworkspaces_test:1',
					'tx_irreworkspaces_test:2',
					'tx_irreworkspaces_test:3',
				),
				'alternativeRecipients' => array(
					'oliver.hader@typo3.org',
				),
			),
		);

		$this->testRecords = array(
			'sys_workspace:' . $this->testWorkspaceId => array(
				'uid' => $this->testWorkspaceId,
				'title' => 'Testing Workspace',
				'stagechg_notification' => 1,
			),
			'tx_irreworkspaces_test:1' => array(
				'uid' => 1,
				'pid' => 99999,
			),
			'tx_irreworkspaces_test:2' => array(
				'uid' => 2,
				'pid' => 99999,
			),
			'tx_irreworkspaces_test:3' => array(
				'uid' => 3,
				'pid' => 99999,
			),
		);

		$this->stageServiceMock = $this->getMock(
			'Tx_Workspaces_Service_Stages',
			array(
				'getStageTitle'
			)
		);
		$this->stageServiceMock->expects($this->any())->method('getStageTitle')
			->will($this->returnValue($this->testStageTitle));

		$this->tceMainMock = $this->getMock(
			't3lib_TCEmain',
			array()
		);

		$this->versionTceMain = $this->getMock(
			'Tx_Version_TceMainFixture',
			array(
				'deliverMail'
			)
		);
		$this->versionTceMain->_setProperty('workspacesStagesService', $this->stageServiceMock);
	}

	/**
	 * Tears down each test case.
	 */
	protected function tearDown() {
		unset($this->testStageTitle);
		unset($this->testComment);
		unset($this->testWorkspaceId);
		unset($this->testStageId);
		unset($this->testRecipients);
		unset($this->testNotifications);
		unset($this->testRecords);
		unset($this->nextRecordCallbackValues);

		unset($this->tceMainMock);

		$GLOBALS['TYPO3_DB'] = $this->originalDatabase;
		unset($this->originalDatabase);
		unset($this->databaseMock);

		unset($GLOBALS['TCA']['tx_irreworkspaces_test']);
		t3lib_div::purgeInstances();
	}

	/**
	 * @test
	 */
	public function XClassHandlingIsActive() {
		$this->assertInstanceOf('ux_tx_version_tcemain', t3lib_div::makeInstance('tx_version_tcemain'));
	}

	/**
	 * @test
	 */
	public function elementAreDeliveredPerTable() {
		$testTableA = uniqid('table_a');
		$testTableB = uniqid('table_b');

		$elements = array(
			array('table' => $testTableA, 'uid' => '1'),
			array('table' => $testTableB, 'uid' => '1'),
			array('table' => $testTableA, 'uid' => '2'),
			array('table' => $testTableB, 'uid' => '2'),
			array('table' => $testTableA, 'uid' => '3'),
			array('table' => $testTableB, 'uid' => '3'),
		);

		$expectedResult = array(
			$testTableA => array('1', '2', '3'),
			$testTableB => array('1', '2', '3'),
		);

		$result = $this->versionTceMain->_callMethod('getElementIdsPerTable', $elements);
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * @test
	 */
	public function singleElementNotificationIsProcessed() {
		$this->versionTceMain->expects($this->once())->method('deliverMail')
			->will($this->returnCallback(array($this, 'assertDeliverMailCallback')));

		$this->versionTceMain->_setProperty('notificationEmailInfo', $this->testNotifications);
		$this->versionTceMain->processCmdmap_afterFinish($this->tceMainMock);
	}

	public function assertDeliverMailCallback(array $recipients, $subject, $message) {
		#echo($message);
		#ob_flush();

		$expectedRecipients = array(
			'oliver.hader@typo3.org',
		);

		$this->assertEquals($expectedRecipients, $recipients);
		$this->assertContains($this->testStageTitle, $message);
		$this->assertContains($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'], $message);
		$this->assertNotEmpty($subject);
	}

	/**
	 * @test
	 */
	public function multipleElementsNotificationsAreProcessed() {
		$this->markTestIncomplete('Not implemented');
	}

	public function selectQueryCallback($fields, $table, $where) {
		$result = FALSE;
		$id = $this->extractValueFromWhereQuery($where, 'uid');

		if ($id !== NULL && isset($this->testRecords[$table . ':' . $id])) {
			$this->nextRecordCallbackValues = array($this->testRecords[$table . ':' . $id]);
			$result = TRUE;
		}

		return $result;
	}

	public function selectQueryRowsCallback($fields, $table, $where) {
		$result = array();

		foreach ($this->testRecords as $identifier => $record) {
			if (strpos($identifier, $table . ':') === 0) {
				$result[] = $record;
			}
		}

		$this->sqlFreeResultCallback();

		return $result;
	}

	public function sqlFetchAssocCallback() {
		$result = NULL;

		if (is_array($this->nextRecordCallbackValues) && count($this->nextRecordCallbackValues)) {
			$result = array_shift($this->nextRecordCallbackValues);
		}

		return $result;
	}

	public function sqlFreeResultCallback() {
		$this->nextRecordCallbackValues = NULL;
	}

	/**
	 * @param string $whereQuery
	 * @param string $field
	 * @return NULL|string
	 */
	protected function extractValueFromWhereQuery($whereQuery, $field) {
		$value = NULL;
		$matches = array();

		$fieldPattern = preg_quote($field, '#');
		if (preg_match('#' . $fieldPattern . '\s*=\s*([^\s]+)#', $whereQuery, $matches)) {
			$value = $matches[1];
			$value = trim($value, '"\'');
		}

		return $value;
	}
}

?>