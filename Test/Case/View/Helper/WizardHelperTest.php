<?php
App::uses('CakeSession', 'Model/Datasource');
App::uses('WizardHelper', 'Wizard.View/Helper');
/**
 * WizardHelperTest Test Case
 *
 * @property WizardHelper $Wizard
 */
class WizardHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$View = new View();
		$this->Wizard = new WizardHelper($View);
		$session = array(
			'config' => array(
				'steps' => array(
					'step1',
					'step2',
					'gender',
					'step3',
					'step4',
					'confirmation',
				),
				'action' => 'wizard',
				'expectedStep' => 'gender',
				'activeStep' => 'gender',
			),
		);
		CakeSession::write('Wizard', $session);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Wizard);
		CakeSession::delete('Wizard');
		parent::tearDown();
	}

	public function testConfigEmpty() {
		CakeSession::delete('Wizard');
		$result = $this->Wizard->config('steps');
		$this->assertNull($result);
	}

	public function testConfigReadAll() {
		$expected = array(
			'steps' => array(
				'step1',
				'step2',
				'gender',
				'step3',
				'step4',
				'confirmation',
			),
			'action' => 'wizard',
			'expectedStep' => 'gender',
			'activeStep' => 'gender',
		);
		$result = $this->Wizard->config();
		$this->assertEquals($expected, $result);
	}

	public function testConfigReadOne() {
		$expected = array(
			'step1',
			'step2',
			'gender',
			'step3',
			'step4',
			'confirmation',
		);
		$result = $this->Wizard->config('steps');
		$this->assertEquals($expected, $result);
	}

	public function testLink() {
		$expected = '<a href="/wizard/gender">gender</a>';
		$result = $this->Wizard->link('gender');
		$this->assertEquals($expected, $result);
	}

	public function testLinkStep() {
		$expected = '<a href="/wizard/gender">Gender</a>';
		$result = $this->Wizard->link('Gender', 'gender');
		$this->assertEquals($expected, $result);
	}

	public function testStepNumberCurrent() {
		$result = $this->Wizard->stepNumber();
		$this->assertEquals(3, $result);
	}

	public function testStepNumberConfirmation() {
		$result = $this->Wizard->stepNumber('confirmation');
		$this->assertEquals(6, $result);
	}

	public function testStepNumberNone() {
		$result = $this->Wizard->stepNumber('step5');
		$this->assertFalse($result);
	}
}
