# Simple Example

Lets create a simple example to show how rapidly a multi-page form can be created with the WizardComponent.

For this example, I am going to be creating a 4 step signup wizard that includes the following steps: 

	1. Account Info
	2. Mailing Address
	3. Billing Info
	4. Review

There will also be a "confirmation" page at the end to confirm the user's signup. This is a very simple example and a wizard like it probably doesn't have a whole lot of real world usefulness, but I just want to demonstrate how the component is used and highlight a couple things as we go along.

It is important to note that though we will using multiple models, the entire wizard will be contained in one controller. Also, I will be using the words 'step' and 'page' interchangeably - I'm merely referring to a page in the multi-page wizard.

So after downloading wizard.php into our project's component folder, we include it in our controller's $components array just as we would any other component:

## Controller Class:

<pre><code>&lt;?php
class SignupController extends AppController {
	var $components = array('Wizard');
}
?&gt;
</code></pre>

Next, we're going to setup our $steps array, which is an ordered list of steps for the wizard to follow. Each step will have its own view and will be processed by its own controller callback method. _There is also another optional callback for each step that will be discussed later._

The steps array is setup in your controller's beforeFilter():

<pre><code>function beforeFilter() {
	$this-&gt;Wizard-&gt;steps = array('account', 'address', 'billing', 'review');
}
</code></pre>

The next step is to create the views used in the signup wizard. The names of the views correspond to steps names included in $steps (account.ctp, address.ctp, etc). I'll include the first view (account.ctp) just to highlight a couple things. 

## View Template:

<pre><code>&lt;?php $form-&gt;create('Signup',array('id'=&gt;'SignupForm','url'=&gt;$this-&gt;here));?&gt;
	&lt;h2&gt;Step 1: Account Information&lt;/h2&gt;
	&lt;ul&gt;
		&lt;li&gt;&lt;?php $form-&gt;input('Client.first_name', array('label'=&gt;'First Name:','size'=&gt;20,'div'=&gt;false));?&gt;&lt;/li&gt;
		&lt;li&gt;&lt;?php $form-&gt;input('Client.last_name', array('label'=&gt;'Last Name:','size'=&gt;20,'div'=&gt;false));?&gt;&lt;/li&gt;
		&lt;li&gt;&lt;?php $form-&gt;input('Client.phone', array('label'=&gt;'Phone Number:','size'=&gt;20,'div'=&gt;false));?&gt;&lt;/li&gt;
	&lt;/ul&gt;
	&lt;ul&gt;
		&lt;li&gt;&lt;?php $form-&gt;input('User.email',&nbsp;array('label'=&gt;'Email:','size'=&gt;20,'div'=&gt;false));?&gt;&lt;/li&gt;
		&lt;li&gt;&lt;?php $form-&gt;input('User.password',array('label'=&gt;'Password:','size'=&gt;20,'div'=&gt;false,));?&gt;&lt;/li&gt;
		&lt;li&gt;&lt;?php $form-&gt;input('User.confirm',array('label'=&gt;'Confirm:','size'=&gt;20,'div'=&gt;false,'type'=&gt;'password'));?&gt;&lt;/li&gt;
	&lt;/ul&gt;
	&lt;div class="submit"&gt;
		&lt;?php $form-&gt;submit('Continue', array('div'=&gt;false));?&gt;
		&lt;?php $form-&gt;submit('Cancel', array('name'=&gt;'Cancel','div'=&gt;false));?&gt;
	&lt;/div&gt;
&lt;?php $form-&gt;end();?&gt;</pre></code>

The first thing I want to point out is the url that the form is submitted to. Rather than submitting to the next step in the wizard, **each step submits to itself**, just as a normal form would do. (My favorite method is above : 'url'=&gt;$this-&gt;here.) This is important because one of my main goals in creating this component was to allow the wizard to be easily setup and easily modified. This meant keeping the views divorced, as much as possible, from their inclusion or position in the steps array. _To further this goal, I have created a WizardHelper that will be published in the bakery soon. In the above example, "Step 1" would be replaced with the $wizard-&gt;stepNumber() method._

The second thing I wanted to highlight was the component's ability to handle data for multiple models (the same as single page forms). This is possible because every step has its own custom callback to process its data.