# Step 2: View Preparation and Data Processing

Next we are going to setup our controller to handle each of the steps in the form wizard.

*Very important:* Rather than creating a separate controller action for each of the steps in the form, all the steps are tied together through one action (the default is 'wizard'). This means, for our example, our urls will look like <code>example.com/signup/wizard/account</code> etc. This way, everything is handle by the component and customization is handled through controller callbacks.

Because of this, the wizard action itself can be very basic. It merely needs to pass the step requested to the component's main method - process():

### Controller Class:

<pre><code>&lt;?php 
class SignupController extends AppController {
	public $components = array('Wizard');

	public function beforeFilter() {
		$this-&gt;Wizard-&gt;steps = array('account', 'address', 'billing', 'review');
	}

	public function wizard($step = null) {
		$this-&gt;Wizard-&gt;process($step);
	}
}
?&gt;</code></pre>

Something to consider if your wizard is the controller's main feature (as it would be in our example), is to route the default action for the controller to the wizard action. This would allow prettier links such as <code>example.com/signup</code> to be handled by SignupController::wizard(), which would then redirect to /signup/wizard/account (or the first incomplete step in the wizard).

<pre><code>Router::connect('/signup', array('controller' =&gt; 'signup', 'action' =&gt; 'wizard'));</code></pre>

Next, we are going to create controller callbacks to handle each step. Each step has two controller callbacks: prepare and process. 

The prepare callback is *optional* and occurs before the step's view is loaded. This is a good place to set any data or variables that you want available for the view. The name of the callback is prepareStepName. So for our example, our prepare callbacks would be prepareAccount(), prepareAddress(), etc.

The process callback is *required* and occurs after data has been posted. This is where data validation should be handled. The process callback must return either true or false. If true, the wizard will continue to the next step; if false, the user will remain on the step and any validation errors will be presented.  The name of the callback is processStepName. So for our example, our process callbacks would be processAccount(), processAddress(), etc. _You do not have to worry about retaining data as this is handled automatically by the component. Data retrieval will be discussed later in the tutorial._


It's very important to note that every step in the wizard must contain a form with a field. The only way for the wizard to continue to the next step is for the process callback to return true. And the process callback is only called if $this-&gt;data is not empty.

So lets create some basic process callbacks. Real world examples would most likely be more complicated, but this should give you the basic idea (don't forget to add any needed models):

### Controller Class:

<pre><code>&lt;?php 
class SignupController extends AppController {
	public $uses = array('Client', 'User', 'Billing');
	public $components = array('Wizard');

	public function beforeFilter() {
		$this-&gt;Wizard-&gt;steps = array('account', 'address', 'billing', 'review');
	}

	public function wizard($step = null) {
		$this-&gt;Wizard-&gt;process($step);
	}
/**
 * [Wizard Process Callbacks]
 */
	public function processAccount() {
		$this-&gt;Client-&gt;set($this-&gt;data);
		$this-&gt;User-&gt;set($this-&gt;data);

		if($this-&gt;Client-&gt;validates() &amp;&amp; $this-&gt;User-&gt;validates()) {
			return true;
		}
		return false;
	}

	public function processAddress() {
		$this-&gt;Client-&gt;set($this-&gt;data);

		if($this-&gt;Client-&gt;validates()) {
			return true;
		}
		return false;
	}

	public function processBilling() {
		$this-&gt;Billing-&gt;set($this-&gt;data);

		if($this-&gt;Billing-&gt;validates()) {
			return true;
		}
		return false;
	}

	public function processReview() {
		return true;
	}
}
?&gt;</code></pre>