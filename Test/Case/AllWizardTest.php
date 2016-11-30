<?php
class AllWizardTest extends CakeTestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('All Wizard tests');
		$suite->addTestDirectoryRecursive(dirname(__FILE__) . DS . 'Controller');
		return $suite;
	}
}
