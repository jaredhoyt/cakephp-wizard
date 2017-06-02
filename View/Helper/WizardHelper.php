<?php
/**
 * Wizard helper by jaredhoyt.
 *
 * Creates links, outputs step numbers for views, and creates dynamic progress menu as the wizard is completed.
 *
 * PHP versions 4 and 5
 * Comments and bug reports welcome at jaredhoyt AT gmail DOT com
 * Licensed under The MIT License
 *
 * @property FormHelper $Form
 * @property SessionHelper $Session
 * @property HtmlHelper $Html
 */
class WizardHelper extends AppHelper {

	public $helpers = array(
		'Session',
		'Html',
		'Form'
	);

	public $output = null;

/**
 * Holds the root of the session key for data storage.
 *
 * @var string
 */
	public $sessionRootKey = 'Wizard';

/**
 * undocumented function
 *
 * @param string $key optional key to retrieve the existing value
 * @return mixed data at config key (if key is passed)
 */
	public function config($key = null) {
		if ($key == null) {
			return $this->Session->read($this->sessionRootKey . '.config');
		} else {
			$wizardData = $this->Session->read($this->sessionRootKey . '.config.' . $key);
			if (!empty($wizardData)) {
				return $wizardData;
			} else {
				return null;
			}
		}
	}

/**
 * undocumented function
 *
 * @param string       $title          The content to be wrapped by `<a>` tags.
 * @param string       $step           Form step.
 * @param array|string $htmlAttributes Array of options and HTML attributes.
 * @param bool|string  $confirmMessage JavaScript confirmation message. This
 *   argument is deprecated as of 2.6. Use `confirm` key in $options instead.
 * @return string link to a specific step
 */
	public function link($title, $step = null, $htmlAttributes = array(), $confirmMessage = false) {
		if ($step == null) {
			$step = $title;
		}
		$url = array(
			'action' => $this->config('action'),
			$step,
		);
		return $this->Html->link($title, $url, $htmlAttributes, $confirmMessage);
	}

/**
 * Retrieve the step number of the specified step name, or the active step
 *
 * @param string     $step       optional name of step
 * @param int|string $shiftIndex optional offset of returned array index. Default 1
 * @return string step number. Returns false if not found
 */
	public function stepNumber($step = null, $shiftIndex = 1) {
		if ($step == null) {
			$step = $this->config('activeStep');
		}
		$steps = $this->config('steps');
		if (in_array($step, $steps)) {
			return array_search($step, $steps) + $shiftIndex;
		} else {
			return false;
		}
	}

/**
 * Counts the total number of steps.
 *
 * @return int
 */
	public function stepTotal() {
		$steps = $this->config('steps');
		return count($steps);
	}

/**
 * Returns a set of html elements containing links for each step in the wizard.
 *
 * @param array|string $titles         Array of form steps where the keys are
 *   the steps and the values are the titles to be used for links. If empty then humanized
 *   step names are used from session.
 * @param array|string $attributes     pass a value for 'wrap' to change the default tag used
 * @param array|string $htmlAttributes Array of options and HTML attributes.
 * @param bool|string  $confirmMessage JavaScript confirmation message. This
 *   argument is deprecated as of 2.6. Use `confirm` key in $options instead.
 * @return string
 */
	public function progressMenu($titles = array(), $attributes = array(), $htmlAttributes = array(), $confirmMessage = false) {
		$wizardConfig = $this->config();
		extract($wizardConfig);
		$wizardAction = $this->config('action');
		$attributes = array_merge(array('wrap' => 'div'), $attributes);
		extract($attributes);
		$incomplete = null;
		foreach ($steps as $title => $step) {
			if (empty($titles[$step])) {
				$title = Inflector::humanize($step);
			} else {
				$title = $titles[$step];
			}
			if (!$incomplete) {
				if ($step == $expectedStep) {
					$incomplete = true;
					$class = 'expected';
				} else {
					$class = 'complete';
				}
				if ($step == $activeStep) {
					$class .= ' active';
				}
				$url = $this->__getStepUrl($step);
				$this->output .= "<$wrap class=\"$class\">";
				$this->output .= $this->Html->link($title, $url, $htmlAttributes, $confirmMessage);
				$this->output .= "</$wrap>";
			} else {
				$this->output .= "<$wrap class=\"incomplete\"><a href=\"#\">$title</a></$wrap>";
			}
		}
		return $this->output;
	}

/**
 * Wrapper for Form->create()
 *
 * @param string $model   The model name for which the form is being defined.
 * @param array  $options An array of html attributes and options.
 *
 * @return string
 */
	public function create($model = null, $options = array()) {
		if (!isset($options['url']) || !in_array($this->request->params['pass'][0], $options['url'])) {
			$options['url'][] = $this->request->params['pass'][0];
		}
		return $this->Form->create($model, $options);
	}

/**
 * Constructs the URL for a given step.
 *
 * @param string $step step action.
 * @return array
 */
	private function __getStepUrl($step) {
		$wizardAction = $this->config('action');
		if ($this->config('persistUrlParams')) {
			$url = Router::reverseToArray($this->request);
			$url['action'] = $this->action;
			$url[0] = $step;
		} else {
			$url = array(
				'action' => $wizardAction,
				$step,
			);
		}
		return $url;
	}
}
