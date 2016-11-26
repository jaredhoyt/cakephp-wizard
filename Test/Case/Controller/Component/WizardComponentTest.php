<?php
App::uses('Controller', 'Controller');
App::uses('WizardComponent', 'Wizard.Controller/Component');

/**
 * AuthTestController class
 *
 * @package       Wizard.Test.Case.Controller.Component
 */
class WizardTestController extends Controller {

	/*public function beforeFilter() {
		$this->Wizard->steps = array('account', 'address', 'billing', 'review');
	}*/

}

/**
 * WizardComponentTest class
 *
 * @package       Wizard.Test.Case.Controller.Component
 */
class WizardComponentTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$CakeRequest = new CakeRequest(null, false);
		$this->Controller = new WizardTestController($CakeRequest, $this->getMock('CakeResponse'));
		$ComponentCollection = new ComponentCollection();
		$ComponentCollection->init($this->Controller);
		$this->Wizard = new WizardComponent($ComponentCollection);
		$this->Controller->Components->init($this->Controller);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Controller, $this->Wizard);
	}

	public function testInitialize() {

	}

}
