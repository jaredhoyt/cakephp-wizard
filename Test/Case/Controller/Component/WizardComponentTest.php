<?php
App::uses('Controller', 'Controller');
App::uses('WizardComponent', 'Wizard.Controller/Component');

/**
 * AuthTestController class
 *
 * @package       Wizard.Test.Case.Controller.Component
 */
class WizardTestController extends Controller {

	public function beforeFilter() {
		//$this->Wizard->steps = array('account', 'address', 'billing', 'review');
		$this->Wizard->steps = array(
			'step1',
			'step2',
			'gender',
			array(
				'male' => array('step3', 'step4'),
				'female' => array('step4', 'step5'),
			),
		);
	}

	/*public function wizard($step = null) {
		$this->Wizard->process($step);
	}

	public function _processAccount() {
		return true;
	}

	public function _processAddress() {
		return true;
	}

	public function _processBilling() {
		return true;
	}

	public function _processReview() {
		return true;
	}*/

}

/**
 * WizardComponentTest class
 *
 * @property WizardComponent $Wizard
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
		//$this->Controller->Components->init($this->Controller);
		$this->Wizard->initialize($this->Controller);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$this->Wizard->Session->delete('Wizard');
		unset($this->Controller, $this->Wizard);
	}

/**
 * Test WizardComponent::initialize().
 *
 * @return void
 */
	public function testInitialize() {
		$this->assertTrue($this->Wizard->controller instanceof WizardTestController);
	}

	public function testConfig() {
		$steps = array('account', 'review');
		$result = $this->Wizard->config('steps', $steps);
		$this->assertEquals($steps, $result);

		$configSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEquals($steps, $configSteps);

		$result = $this->Wizard->config('steps');
		$this->assertEquals($steps, $result);
	}

	public function testBranch() {
		$this->Wizard->branch('female');
		$expectedBranches = array(
			'female' => 'branch',
		);
		$sessionBranches = $this->Wizard->Session->read('Wizard.branches');
		$this->assertEquals($steps, $sessionBranches);
	}
}
