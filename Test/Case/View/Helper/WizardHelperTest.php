<?php
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
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Wizard);
		$this->Wizard->Session->delete('Wizard');
		parent::tearDown();
	}

	public function testConfigEmpty() {
		$result = $this->Wizard->config('steps');
		$this->assertNull($result);
	}

	public function testConfigReadAll() {
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
				'expectedStep' => 'confirmation',
				'activeStep' => 'confirmation',
			),
		);
		$this->Wizard->Session->write('Wizard', $session);

		$result = $this->Wizard->config();
		$this->assertEquals($session['config'], $result);
	}

	public function testConfigReadOne() {
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
				'expectedStep' => 'confirmation',
				'activeStep' => 'confirmation',
			),
		);
		$this->Wizard->Session->write('Wizard', $session);

		$result = $this->Wizard->config('steps');
		$this->assertEquals($session['config']['steps'], $result);
	}
}
