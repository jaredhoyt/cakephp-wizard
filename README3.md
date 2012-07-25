# Step 3: Data Retrieval and Wizard Completion

At this point in the tutorial, your wizard should have of four steps - each consisting of a view and process callback (plus any optional prepare callbacks). Also, the wizard should be automatically handling data persistence and navigation between the steps. The next question is how to retrieve the data stored by the component and what happens at the completion of the wizard.

## Data Retrieval

Retrieving data from the component is possible at any point in the wizard. While our example will not manipulate or store the data permanently until the completion of the wizard, it's also reasonable that some applications may need to store data before the end of the wizard. For example, a job application may not be completed in one session but rather over a period of time. The progress, then, would need to be kept up with between sessions, rather than manipulated/stored all at once during the wizard completion.
 
Wizard data is stored with the following path: sessionKey.stepName.modelName.fieldName. The sessionKey will be explained in the Wizard Completion section below. The component method for retrieving data is read($key = null) which works pretty much like <code>SessionComponent::read()</code> except that the sessionKey is handled automatically by the WizardComponent and doesn't need to be passed into read(). Passing null into read() returns all Wizard data.

So, for example, if we wanted to do something with the client's email address (which was obtained in the account step) while processing the review step, we would use the following code:

<pre><code>public function processReview() {
	$email = $this->Wizard->read('account.User.email');
	/* do something with the $email here */

	return true;
}</code></pre>
      
An example showing how to retrieve all the current data with read() will be given below.

## Wizard Completion

One of my goals when writing this component was to prevent double submission of user data. One of the ways I accomplished this was by using the process callbacks for each step and redirecting to rather than rendering the next step.

The second way was including an extra redirect and callback during the wizard completion process that creates a sort of "no man's land" for the wizard data. The way this works is, after the process callback for the last step is completed, the wizard data is moved to a new location in the session (Wizard.complete), the wizard redirects to a null step and another callback is called: _afterComplete(). 

_afterComplete() is an optional callback and is the ideal place to manipulate/store data after the wizard has been completed by the user. The callback does not need to return anything and the component automatically redirects to the $completeUrl (default '/') after the callback is finished.

It's important to note that immediately after the afterComplete() callback and before the user is redirected to $completeUrl, the wizard is reset completely (all data is flushed from the session). If you need to redirect manually from _afterComplete(), be sure to call <code>Wizard->reset()</code> manually.

So, to complete our tutorial example, we will pull all the data out of the wizard, store it in our database, and redirect the user to a confirmation page. 

### Controller Class:

<pre><code><?php 
class SignupController extends AppController {
	public $uses = array('Client', 'User', 'Billing');
	public $components = array('Wizard');

	public function beforeFilter() {
		$this->Wizard->steps = array('account', 'address', 'billing', 'review');
		$this->Wizard->completeUrl = '/signup/confirm';
	}

	public function confirm() {
	}

	public function wizard($step = null) {
		$this->Wizard->process($step);
	}
/**
 * [Wizard Process Callbacks]
 */
	protected function _processAccount() {
		$this->Client->set($this->data);
		$this->User->set($this->data);

		if($this->Client->validates() &amp;&amp; $this->User->validates()) {
			return true;
		}
		return false;
	}

	protected function _processAddress() {
		$this->Client->set($this->data);

		if($this->Client->validates()) {
			return true;
		}
		return false;
	}

	protected function _processBilling() {
		$this->Billing->set($this->data);

		if($this->Billing->validates()) {
			return true;
		}
		return false;
	}

	protected function _processReview() {
		return true;
	}
/**
 * [Wizard Completion Callback]
 */
	protected function _afterComplete() {
		$wizardData = $this->Wizard->read();
		extract($wizardData);

		$this->Client->save($account['Client'], false, array('first_name', 'last_name', 'phone'));
		$this->User->save($account['User'], false, array('email', 'password'));
		
		... etc ...
	}
}
?></code></pre>

Please note the addition to beforeFilter() and the new confirm() method. You would also need to create a view file (confirm.ctp) with something like "Congrats, your sign-up was successful!" etc. It would also be good to create some sort of token during the _afterComplete() callback and have it checked for in the confirm() method, but that's outside the scope of this tutorial. 