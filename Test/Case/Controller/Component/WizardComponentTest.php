<?php
App::uses('Controller', 'Controller');
App::uses('WizardComponent', 'Wizard.Controller/Component');

/**
 * AuthTestController class
 *
 * @package       Wizard.Test.Case.Controller.Component
 */
class WizardTestController extends Controller {

	public $autoRender = false;

	public $components = array(
		'Session',
		'Wizard.Wizard' => array(
			'steps' => array(
				'step1',
				'step2',
				'gender',
				array(
					'male' => array('step3', 'step4'),
					'female' => array('step4', 'step5'),
					'unknown' => 'step6',
				),
				'confirmation',
			),
		),
	);

	public function wizard($step = null) {
		$this->Wizard->process($step);
	}

	public function _processStep1() {
		if (!empty($this->request->data)) {
			return true;
		}
		return false;
	}

	public function _processStep2() {
		if (!empty($this->request->data)) {
			return true;
		}
		return false;
	}

	public function _processStep3() {
		if (!empty($this->request->data)) {
			return true;
		}
		return false;
	}

	public function _processStep4() {
		if (!empty($this->request->data)) {
			return true;
		}
		return false;
	}

	public function _processStep5() {
		if (!empty($this->request->data)) {
			return true;
		}
		return false;
	}

	public function _processGender() {
		if (!empty($this->request->data)) {
			return true;
		}
		return false;
	}

	public function _processConfirmation() {
		return true;
	}

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
		$this->Controller->Components->init($this->Controller);
		$this->Wizard = $this->Controller->Wizard;
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
			'WizardTest' => array(
				'female' => 'branch',
			),
		);
		$sessionBranches = $this->Wizard->Session->read('Wizard.branches');
		$this->assertEquals($expectedBranches, $sessionBranches);
	}

	public function testBranchSkip() {
		$this->Wizard->branch('female', true);
		$expectedBranches = array(
			'WizardTest' => array(
				'female' => 'skip',
			),
		);
		$sessionBranches = $this->Wizard->Session->read('Wizard.branches');
		$this->assertEquals($expectedBranches, $sessionBranches);
	}

	public function testBranchOverwrite() {
		$this->Wizard->branch('male');
		$this->Wizard->branch('female');
		$expectedBranches = array(
			'WizardTest' => array(
				'male' => 'branch',
				'female' => 'branch',
			),
		);
		$sessionBranches = $this->Wizard->Session->read('Wizard.branches');
		$this->assertEquals($expectedBranches, $sessionBranches);

		$this->Wizard->branch('male', true);
		$expectedBranches = array(
			'WizardTest' => array(
				'male' => 'skip',
				'female' => 'branch',
			),
		);
		$sessionBranches = $this->Wizard->Session->read('Wizard.branches');
		$this->assertEquals($expectedBranches, $sessionBranches);
	}

	public function testStartup() {
		$configAction = $this->Wizard->Session->read('Wizard.config.action');
		$this->assertEmpty($configAction);
		$configSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEmpty($configSteps);
		$this->assertEmpty($this->Wizard->controller->helpers);

		$this->Wizard->action = 'gender';
		$this->Wizard->startup($this->Controller);

		$expectedAction = 'gender';
		$resultAction = $this->Wizard->Session->read('Wizard.config.action');
		$this->assertEquals($expectedAction, $resultAction);
		$expectedSteps = array(
			'step1',
			'step2',
			'gender',
			'step3',
			'step4',
			'confirmation',
		);
		$resultSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEquals($expectedSteps, $resultSteps);
		$this->assertEquals($expectedSteps, $this->Wizard->steps);
		$expectedHelpers = array(
			'Wizard.Wizard',
		);
		$this->assertEquals($expectedHelpers, $this->Wizard->controller->helpers);
	}

	public function testStartupSkipBranch() {
		$configSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEmpty($configSteps);

		$this->Wizard->branch('male', true);
		$this->Wizard->branch('female', true);
		$this->Wizard->action = 'gender';
		$this->Wizard->startup($this->Controller);

		$expectedSteps = array(
			'step1',
			'step2',
			'gender',
			'step6',
			'confirmation',
		);
		$resultSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEquals($expectedSteps, $resultSteps);
		$this->assertEquals($expectedSteps, $this->Wizard->steps);
	}

	public function testStartupBranch() {
		$configSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEmpty($configSteps);

		$this->Wizard->branch('female');
		$this->Wizard->action = 'gender';
		$this->Wizard->startup($this->Controller);

		$expectedSteps = array(
			'step1',
			'step2',
			'gender',
			'step4',
			'step5',
			'confirmation',
		);
		$resultSteps = $this->Wizard->Session->read('Wizard.config.steps');
		$this->assertEquals($expectedSteps, $resultSteps);
		$this->assertEquals($expectedSteps, $this->Wizard->steps);
	}

	public function testStepGetOne() {
		$this->Wizard->action = 'step1';
		$this->Wizard->startup($this->Controller);
		$this->Wizard->process('step1');

		$expectedSession = array(
			'config' => array(
				'steps' => array(),
				'branches' => array(),
			),
		);
		$resultSession = $this->Wizard->Session->read('Wizard');
		$this->assertEquals($expectedSession, $resultSession);
	}
}
