<?php
require_once 'PHPUnit\Framework\TestCase.php';
require_once '..\source\ShellExecuter.php';

/**
 * test case.
 */
class TestExecuter extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException Exception
	 */
	public function testFailure() {
		$se = new ShellExecuter("sleep 5", 1);
		$se->execute();
	}

	public function testOutput() {
		$se = new ShellExecuter("echo hi");
		$result = $se->execute();
		$this->assertEquals("hi", $result, "Testing shell output");
	}


	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated Events::tearDown()
		parent::tearDown();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
}

