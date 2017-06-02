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

	public function testStepTotal() {
		$result = $this->Wizard->stepTotal();
		$this->assertEquals(6, $result);
	}

	public function testProgressMenu() {
		$expected = '<div class="complete"><a href="/wizard/step1">Step1</a></div>';
		$expected .= '<div class="complete"><a href="/wizard/step2">Step2</a></div>';
		$expected .= '<div class="expected active"><a href="/wizard/gender">Gender</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Step3</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Step4</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Confirmation</a></div>';
		$result = $this->Wizard->progressMenu();
		$this->assertEquals($expected, $result);
	}

	public function testProgressMenuCustomWrapper() {
		$expected = '<li class="complete"><a href="/wizard/step1">Step1</a></li>';
		$expected .= '<li class="complete"><a href="/wizard/step2">Step2</a></li>';
		$expected .= '<li class="expected active"><a href="/wizard/gender">Gender</a></li>';
		$expected .= '<li class="incomplete"><a href="#">Step3</a></li>';
		$expected .= '<li class="incomplete"><a href="#">Step4</a></li>';
		$expected .= '<li class="incomplete"><a href="#">Confirmation</a></li>';
		$result = $this->Wizard->progressMenu(array(), array('wrap' => 'li'));
		$this->assertEquals($expected, $result);
	}

	public function testProgressMenuCustomTitles() {
		$expected = '<div class="complete"><a href="/wizard/step1">Credentials</a></div>';
		$expected .= '<div class="complete"><a href="/wizard/step2">Address</a></div>';
		$expected .= '<div class="expected active"><a href="/wizard/gender">Gender</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Shipping Address</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Payment</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Confirmation</a></div>';

		$titles = array(
			'step1' => 'Credentials',
			'step2' => 'Address',
			'gender' => 'Gender',
			'step3' => 'Shipping Address',
			'step4' => 'Payment',
			'confirmation' => 'Confirmation',
		);
		$result = $this->Wizard->progressMenu($titles);
		$this->assertEquals($expected, $result);
	}

	public function testProgressMenuPersistUrlParams() {
		$url = '/wizard_test/wizard/gender/123?x=7&y=9';
		$CakeRequest = new CakeRequest($url, true);
		$CakeRequest->addParams(Router::parse($url));
		$Controller = new Controller($CakeRequest, new CakeResponse());
		$View = new View($Controller);
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
				'persistUrlParams' => true,
			),
		);
		CakeSession::write('Wizard', $session);

		$expected = '<div class="complete"><a href="/wizard_test/wizard/step1/123?x=7&amp;y=9">Step1</a></div>';
		$expected .= '<div class="complete"><a href="/wizard_test/wizard/step2/123?x=7&amp;y=9">Step2</a></div>';
		$expected .= '<div class="expected active"><a href="/wizard_test/wizard/gender/123?x=7&amp;y=9">Gender</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Step3</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Step4</a></div>';
		$expected .= '<div class="incomplete"><a href="#">Confirmation</a></div>';
		$result = $this->Wizard->progressMenu();
		$this->assertEquals($expected, $result);
	}
}
