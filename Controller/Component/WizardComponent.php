<?php
/**
 * Wizard component by jaredhoyt.
 *
 * Handles multi-step form navigation, data persistence, validation callbacks, and plot-branching navigation.
 *
 * PHP versions 4 and 5
 *
 * Comments and bug reports welcome at jaredhoyt AT gmail DOT com
 *
 * Licensed under The MIT License
 *
 * @property $Session SessionComponent
 * @writtenby          jaredhoyt
 * @license            http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class WizardComponent extends Component {

/**
 * Controller $controller variable.
 *
 * @var Controller
 * @access public
 */
	public $controller = null;

/**
 * The Component will redirect to the "expected step" after a step has been successfully
 * completed if autoAdvance is true. If false, the Component will redirect to
 * the next step in the $steps array. (This is helpful for returning a user to
 * the expected step after editing a previous step w/o them having to navigate through
 * each step in between.)
 *
 * @var boolean
 * @access public
 */
	public $autoAdvance = true;

/**
 * Option to automatically reset if the wizard does not follow "normal"
 * operation. (ie. manual url changing, navigation away and returning, etc.)
 * Set this to false if you want the Wizard to return to the "expected step"
 * after invalid navigation.
 *
 * @var boolean
 * @access public
 */
	public $autoReset = false;

/**
 * If no processCallback() exists for the current step, the component will automatically
 * validate the model data against the models included in the controller's uses array.
 *
 * @var boolean
 * @access public
 */
	public $autoValidate = false;

/**
 * List of steps, in order, that are to be included in the wizard.
 *        basic example: $steps = array('contact', 'payment', 'confirm');
 *
 * The $steps array can also contain nested steps arrays of the same format but must be wrapped by a branch group.
 *        plot-branched example: $steps = array('job_application', array('degree' => array('college', 'degree_type'), 'nodegree' => 'experience'), 'confirm');
 *
 * The 'branchnames' (ie 'degree', 'nodegree') are arbitrary but used as selectors for the branch() and unbranch() methods. Branches
 * can point to either another steps array or a single step. The first branch in a group that hasn't been skipped (see branch())
 * is included by default (if $defaultBranch = true).
 *
 * @var array
 * @access public
 */
	public $steps = array();

/**
 * Controller action that processes your step.
 *
 * @var string
 * @access public
 */
	public $action = 'wizard';

/**
 * Url to be redirected to after the wizard has been completed.
 * Controller::afterComplete() is called directly before redirection.
 *
 * @var mixed
 * @access public
 */
	public $completeUrl = '/';

/**
 * Url to be redirected to after 'Cancel' submit button has been pressed by user.
 *
 * @var mixed
 * @access public
 */
	public $cancelUrl = '/';

/**
 * Url to be redirected to after 'Draft' submit button has been pressed by user.
 *
 * @var mixed
 * @access public
 */
	public $draftUrl = '/';

/**
 * If true, the first "non-skipped" branch in a group will be used if a branch has
 * not been included specifically.
 *
 * @var boolean
 * @access public
 */
	public $defaultBranch = true;

/**
 * If true, the user will not be allowed to edit previously completed steps. They will be
 * "locked down" to the current step. The opposite of $roaming.
 *
 * @var boolean
 * @access public
 */
	public $lockdown = false;

/**
 * If true, the user will be allowed navigate to any steps. The opposite of $lockdown.
 *
 * @var boolean
 * @access public
 */
	public $roaming = false;

/**
 * If true, the component will render views found in views/{wizardAction}/{step}.ctp rather
 *  than views/{step}.ctp.
 *
 * @var boolean
 * @access public
 */
	public $nestedViews = false;

/**
* Other components used.
*
* @var array
* @access public
*/
	public $components = array('Session');

/**
 * Internal step tracking.
 *
 * @var string
 * @access protected
 */
	protected $_currentStep = null;

/**
 * Holds the session key for data storage.
 *
 * @var string
 * @access protected
 */
	protected $_sessionKey = null;

/**
 * Other session keys used.
 *
 * @var string
 * @access protected
 */
	protected $_configKey = null;

	protected $_branchKey = null;

/**
 * Holds the array based url for redirecting.
 *
 * @var array
 * @access protected
 */
	protected $_wizardUrl = array();

/**
 * WizardComponent Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy-load its components
 * @param array               $settings   Array of configuration settings
 *
 * @access public
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->_set($settings);
	}

/**
 * Initializes WizardComponent for use in the controller
 *
 * @param \Controller|object $controller A reference to the instantiating controller object
 *
 * @access public
 * @return void
 */
	public function initialize(Controller $controller) {
		$this->controller = $controller;

		$this->_sessionKey = $this->controller->Session->check('Wizard.complete') ? 'Wizard.complete' : 'Wizard.' . $controller->name;
		$this->_configKey = 'Wizard.config';
		$this->_branchKey = 'Wizard.branches.' . $controller->name;
	}

/**
 * Component startup method.
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param \Controller|object $controller A reference to the instantiating controller object
 *
 * @access public
 */
	public function startup(Controller $controller) {
		$this->steps = $this->_parseSteps($this->steps);

		$this->config('action', $this->action);
		$this->config('steps', $this->steps);

		if (!in_array('Wizard.Wizard', $this->controller->helpers) && !array_key_exists('Wizard.Wizard', $this->controller->helpers)) {
			$this->controller->helpers[] = 'Wizard.Wizard';
		}
	}

/**
 * Parses the steps array by stripping off nested arrays not included in the branches
 * and returns a simple array with the correct steps.
 *
 * @param array $steps Array to be parsed for nested arrays and returned as simple array.
 *
 * @return array
 * @access protected
 */
	protected function _parseSteps($steps) {
		$parsed = array();

		foreach ($steps as $key => $name) {
			if (is_array($name)) {
				foreach ($name as $branchName => $step) {
					$branchType = $this->_branchType($branchName);

					if ($branchType) {
						if ($branchType !== 'skip') {
							$branch = $branchName;
						}
					} elseif (empty($branch) && $this->defaultBranch) {
						$branch = $branchName;
					}
				}

				if (!empty($branch)) {
					if (is_array($name[$branch])) {
						$parsed = array_merge($parsed, $this->_parseSteps($name[$branch]));
					} else {
						$parsed[] = $name[$branch];
					}
				}

				unset($branch);
			} else {
				$parsed[] = $name;
			}
		}
		return $parsed;
	}

/**
 * Saves configuration details for use in WizardHelper.
 *
 * @param $branch
 *
 * @return mixed
 * @access protected
 */
	protected function _branchType($branch) {
		if ($this->controller->Session->check("$this->_branchKey.$branch")) {
			return $this->controller->Session->read("$this->_branchKey.$branch");
		}
		return false;
	}

/**
 * Saves configuration details for use in WizardHelper or returns a config value.
 * This is method usually handled only by the component.
 *
 * @param string $name  Name of configuration variable.
 * @param mixed  $value Value to be stored.
 *
 * @return mixed
 * @access public
 */
	public function config($name, $value = null) {
		if ($value == null) {
			// $this->controller->Session->read("$this->_configKey")
			return $this->controller->Session->read("$this->_configKey.$name");
		}
		$this->controller->Session->write("$this->_configKey.$name", $value);
		return $value;
	}

/**
 * Main Component method.
 *
 * @param string $step Name of step associated in $this->steps to be processed.
 *
 * @throws NotImplementedException
 * @return bool|\CakeResponse
 * @access public
 */
	public function process($step) {
		if (isset($this->controller->request->data['Cancel'])) {
			if (method_exists($this->controller, '_beforeCancel')) {
				$this->controller->_beforeCancel($this->_getExpectedStep());
			}
			$this->reset();
			$this->controller->redirect($this->cancelUrl);
		}
		if (isset($this->controller->request->data['Draft'])) {
			if (method_exists($this->controller, '_saveDraft')) {
				$draft = array(
					'_draft' => array(
						'current' => array(
							'step' => $step,
							'data' => $this->controller->request->data
						)
					)
				);
				$this->controller->_saveDraft(array_merge_recursive((array)$this->read(), $draft));
			}

			$this->reset();
			$this->controller->redirect($this->draftUrl);
		}

		if (empty($step)) {
			if ($this->controller->Session->check('Wizard.complete')) {
				if (method_exists($this->controller, '_afterComplete')) {
					$this->controller->_afterComplete();
				}
				$this->reset();
				$this->controller->redirect($this->completeUrl);
			}

			$this->autoReset = false;
		} elseif ($step == 'reset') {
			if (!$this->lockdown) {
				$this->reset();
			}
		} else {
			if ($this->_validStep($step)) {
				$this->_setCurrentStep($step);

				if (!empty($this->controller->data) && !isset($this->controller->request->data['Previous'])) {
					$processCallback = '_' . Inflector::variable('process_' . $this->_currentStep);
					if (method_exists($this->controller, $processCallback)) {
						$proceed = $this->controller->$processCallback();
					} elseif ($this->autoValidate) {
						$proceed = $this->_validateData();
					} else {
						throw new NotImplementedException(sprintf(__('Process Callback not found. Please create Controller::%s', $processCallback)));
					}

					if ($proceed) {
						$this->save();

						if (isset($this->controller->request->data['SaveAndBack']) && prev($this->steps)) {
							$this->redirect(current($this->steps));
						}
						if (next($this->steps)) {
							if ($this->autoAdvance) {
								$this->redirect();
							}
							$this->redirect(current($this->steps));
						} else {
							$this->controller->Session->write('Wizard.complete', $this->read());
							$this->reset();
							$this->controller->redirect(array('action' => $this->action));
						}
					}
				} elseif (isset($this->controller->request->data['Previous']) && prev($this->steps)) {
					$this->redirect(current($this->steps));
				} elseif ($this->controller->Session->check("$this->_sessionKey._draft.current")) {
					$this->controller->data = $this->read('_draft.current.data');
					$this->controller->Session->delete("$this->_sessionKey._draft.current");
				} elseif ($this->controller->Session->check("$this->_sessionKey.$this->_currentStep")) {
					$this->controller->data = $this->read($this->_currentStep);
				}

				$prepareCallback = '_' . Inflector::variable('prepare_' . $this->_currentStep);
				if (method_exists($this->controller, $prepareCallback)) {
					$this->controller->$prepareCallback();
				}

				$this->config('activeStep', $this->_currentStep);

				if ($this->nestedViews) {
					$this->controller->viewPath .= '/' . $this->action;
				}

				return $this->controller->autoRender ? $this->controller->render($this->_currentStep) : true;
			} else {
				$this->redirect();
			}
		}

		if ($step != 'reset' && $this->autoReset) {
			$this->reset();
		}

		$this->redirect();
	}

/**
 * Finds the first incomplete step (i.e. step data not saved in Session).
 *
 * @return string $step or false if complete
 * @access protected
 */
	protected function _getExpectedStep() {
		foreach ($this->steps as $step) {
			if (!$this->controller->Session->check("$this->_sessionKey.$step")) {
				$this->config('expectedStep', $step);
				return $step;
			}
		}
		return false;
	}

/**
 * Resets the wizard by deleting the wizard session.
 *
 * @access public
 */
	public function reset() {
		$this->controller->Session->delete($this->_branchKey);
		$this->controller->Session->delete($this->_sessionKey);
	}

/**
 * Get the data from the Session that has been stored by the WizardComponent.
 *
 * @param null $key
 *
 * @internal param mixed $name The name of the session variable (or a path as sent to Set.extract)
 *
 * @return mixed The value of the session variable
 * @access   public
 */
	public function read($key = null) {
		if ($key == null) {
			return $this->controller->Session->read($this->_sessionKey);
		} else {
			$wizardData = $this->controller->Session->read("$this->_sessionKey.$key");
			return !empty($wizardData) ? $wizardData : null;
		}
	}

/**
 * Validates the $step four ways:
 *   1. Explicitly only validate step that exists in $this->steps array.
 *   2. If $roaming option is true any steps within $this->steps is valid
 *   3. If $lockdown option is true only the next/current step is valid.
 *   4. If $roaming and $lockdown is false validate the step either before or exactly the expected step.
 *
 * @param $step Step to validate.
 *
 * @return mixed
 * @access protected
 */
	protected function _validStep($step) {
		if (in_array($step, $this->steps)) {
			if ($this->roaming) {
				return true;
			} elseif ($this->lockdown) {
				return (array_search($step, $this->steps) == array_search($this->_getExpectedStep(), $this->steps));
			}
			return (array_search($step, $this->steps) <= array_search($this->_getExpectedStep(), $this->steps));
		}
		return false;
	}

/**
 * Moves internal array pointer of $this->steps to $step and sets $this->_currentStep.
 *
 * @param string $step, Step to point to.
 *
 * @access protected
 */
	protected function _setCurrentStep($step) {
		$this->_currentStep = reset($this->steps);

		while (current($this->steps) != $step) {
			$this->_currentStep = next($this->steps);
		}
	}

/**
 * Validates controller data with the correct model if the model is included in
 * the controller's uses array. This only occurs if $autoValidate = true and there
 * is no processCallback in the controller for the current step.
 *
 * @return boolean
 * @access protected
 */
	protected function _validateData() {
		$controller =& $this->controller;

		foreach ($controller->request->data as $model => $data) {
			if (in_array($model, $controller->uses)) {
				$controller->{$model}->set($data);

				if (!$controller->{$model}->validates()) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * Saves the data from the current step into the Session.
 *
 * Please note: This is normally called automatically by the component after
 * a successful _processCallback, but can be called directly for advanced navigation purposes.
 *
 * @access public
 */
	public function save($step = null, $data = null) {
		if (is_null($step)) {
			$step = $this->_currentStep;
		}
		if (is_null($data)) {
			$data = $this->controller->request->data;
		}
		$this->controller->Session->write("$this->_sessionKey.$step", $data);
	}

/**
 * Handles Wizard redirection. A null url will redirect to the "expected" step.
 *
 * @param string  $step   Stepname to be redirected to.
 * @param integer $status Optional HTTP status code (eg: 404)
 * @param boolean $exit   If true, exit() will be called after the redirect
 *
 * @see    Controller::redirect()
 * @access public
 */
	public function redirect($step = null, $status = null, $exit = true) {
		if ($step == null) {
			$step = $this->_getExpectedStep();
		}
		$url = array(
			'controller' => $this->controller->request->controller,
			'action' => $this->action,
			$step
		);
		$this->controller->redirect($url, $status, $exit);
	}

/**
 * Selects a branch to be used in the steps array. The first branch in a group is included by default.
 *
 * @param string  $name Branch name to be included in steps.
 * @param boolean $skip Branch will be skipped instead of included if true.
 *
 * @access public
 */
	public function branch($name, $skip = false) {
		$branches = array();

		if ($this->controller->Session->check($this->_branchKey)) {
			$branches = $this->controller->Session->read($this->_branchKey);
		}

		if (isset($branches[$name])) {
			unset($branches[$name]);
		}

		$value = $skip ? 'skip' : 'branch';
		$branches[$name] = $value;

		$this->controller->Session->write($this->_branchKey, $branches);
	}

/**
 * Loads previous draft session.
 *
 * @param array $draft Session data of same format passed to Controller::_saveDraft()
 *
 * @see    WizardComponent::process()
 * @access public
 */
	public function loadDraft($draft = array()) {
		if (!empty($draft['_draft']['current']['step'])) {
			$this->restore($draft);
			$this->redirect($draft['_draft']['current']['step']);
		}
		$this->redirect();
	}

/**
 * Sets data into controller's wizard session. Particularly useful if the data
 * originated from WizardComponent::read() as this will restore a previous session.
 *
 * @param array $data Data to be written to controller's wizard session.
 *
 * @access public
 */
	public function restore($data = array()) {
		$this->controller->Session->write($this->_sessionKey, $data);
	}

/**
 * Resets the wizard by deleting the wizard session.
 *
 * @access public
 */
	public function resetWizard() {
		$this->reset();
	}

/**
 * Resets the data from the Session that has been stored by the WizardComponent.
 *
 * @param null $key
 *
 * @internal param mixed $name The name of the session variable (or a path as sent to Set.extract)
 *
 * @access   public
 */
	public function delete($key = null) {
		if ($key == null) {
			return;
		} else {
			$this->controller->Session->delete("$this->_sessionKey.$key");
			return;
		}
	}

/**
 * Removes a branch from the steps array.
 *
 * @param string $branch Name of branch to be removed from steps array.
 *
 * @access public
 */
	public function unbranch($branch) {
		$this->controller->Session->delete("$this->_branchKey.$branch");
	}
        

}
