# Step 4: Plot-Branching Navigation

A new addition to the WizardComponent 1.2 is *plot-branching navigation* (pbn). If you ever read a book as a child in which you interacted with the plot - i.e. If the knight slays the dragon, turn to page 64, if the knight runs for safety, turn to page 82. - then you've experienced pbn. In some applications, the steps in a wizard may not be a simple linear path, but might instead require the ability to "change course" based on user input.

For example, a survey that has varying questions for men or women might ask gender on the first page and would then need to navigate to different pages depending on the answer. While this is a simple example, some wizards can become very complicated when all the different options occur at different points in the wizard and "paths" begin to cross.

In some instances, it may not be a different path altogether, but merely a step being skipped over. Integrating Paypal Pro, for instance, requires the application allow the user to either enter their billing information on the site, or hop over to Paypal, login to their account and "skip" the billing page on the original site.

## Advanced $steps Array

When using pbn, the $steps array becomes a bit more complex. Instead of adding/removing steps on the fly, all the steps are included into the array like they normally would. Then, "branches" are selected or skipped using the component methods. The trick to understanding the WizardComponent's pbn implementation is understanding the $steps array - the rest is pretty simple.

A simple $steps array is a single-tiered structure with each element corresponding to a step in the wizard. The array is ordered and the steps are handled sequentially.

An advanced $steps array setup for pbn is a multi-tiered structure consisting of simple $steps arrays separated by branch arrays (or branch groups). The branch arrays are associative arrays with branch names as indexes and simple $steps arrays as elements. 

For example, lets say we had six steps: step1, step2, gender, step3, step4, and step5. The gender step would determine the user's gender and the subsequent steps would vary accordingly. If male, step3 and step4 would be used; if female, step4 and step5 would be used. So lets setup our $steps array:

<pre><code>public function beforeFilter() {
	$this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3', 'step4'), 'female' => array('step4', 'step5')));
}</code></pre>

It's important to understand that there is almost always more than one way to accomplish the same effect with different $steps arrays. For example, I could have instead, setup a 'male' branch that used step3, included step4 for both, and then another branch for 'female' that would include step5.

<pre><code>public function beforeFilter() {
	$this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3')), 'step4', array('female' => array('step5')));
}</code></pre>

Also, although these examples are simple, I should point out that the $steps array is not limited to a three-tiered array. As long as the pattern is followed - <code>array(stepName, array(branchName => array(stepName, etc...)))</code> - the steps array can be as complex as resources allow for. 