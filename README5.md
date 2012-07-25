# Step 5: PBN Component Methods

After the the $steps array is setup, the question becomes, "How does the component navigate through all the branches?" This is done be selecting which branch will be used in a "branch group". By default, the first branch in a group is always used (unless it has been "skipped" - more on that later). You can turn this feature off by setting Wizard->defaultBranch = false.

So, lets look at our two previous examples:

<pre><code>*Example 1:*
$this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3', 'step4'), 'female' => array('step4', 'step5')));

*Example 2:*
$this->Wizard->steps = array('step1', 'step2', 'gender', array('male' => array('step3')), 'step4', array('female' => array('step5')));</code></pre>

In example 1, 'male' and 'female' are two branches in the same branch group. Therefore, without any interference, the component would automatically use the 'male' branch and 'female' would be skipped. The steps would occur: step1, step2, gender, step3, step4. If $defaultBranch = false, both would be skipped and the steps would occur: step1, step2, gender.

In example 2, 'male' and 'female' are in separate branch groups. Therefore, without any interference, both branches would be used since they are the first branch in their respective groups. The steps would occur: step1, step2, gender, step3, step4, step5.  If $defaultBranch = false, both would be skipped and the steps would occur: step1, step2, gender, step4.

## branch() and unbranch()

In order to specify to the component which branches should be used, you must use the branch() and unbranch() methods. The branch() method includes a branch (specified by its name) in the session and unbranch() removes a branch from the session. branch() also has an extra parameter that allows branches to be easily skipped - more on that below.

So lets assume "female" was selected on the gender step. During the "processGender" callback, we could specify the "female" branch to be included:

<pre><code>public  function processGender() {
	$this->Client->set($this->data);

	if($this->Client->validates()) {
		if($this->data['Client']['gender'] == 'female') {
			 $this->Wizard->branch('female');
		} else {
			 $this->Wizard->branch('male');
		}
		return true;
	}
	return false;
}</code></pre>

In example 1, the 'female' branch would be used instead of the 'male' branch and the steps would occur: step1, step2, gender, step4, step5. However, in example 2, unless $defaultBranch = false, the 'male' branch would also be used since it is not in the same branch group as 'female'.

Important: The first branch that has been included in the session will be used. In other words, if you were to do branch('male') and branch('female') for example 1, 'male' would be used since it occurs before 'female'. If 'male' was branched previously and you later wanted 'female' to be used, you would need to use unbranch('male').

In addition to including a branch to be used, branch() can also specify branches to be "skipped" by setting the second parameter to 'true'. If, for example, we used Wizard->branch('male', true) in the previous examples, 'male' would be skipped and 'female' would be used. The steps would occur: step1, step2, gender, step4, step5  - the same as using branch('female') with $defaultBranch = true! 

The last thing I want to mention about pbn is that branch names do not necessarily have to be unique. In fact, I'd imagine some complex pbn wizards could be solved with some creative branch naming schemes in which identical branch names would be used only one branch() would have to be called to alter multiple branch groups. For example, using branch('male') with the following $steps array would select the 'male' branches in both the first and second branch groups.

<pre><code>$steps = array('step1', array('male' => ..., 'female' => ...), 'step2', array('cyborg' => ..., 'male' => ..., 'alien' => ...)); </code></pre>

Also, (the other last thing I want to mention), the $steps array that each branch name points to can be treated exactly the same as the main $steps array - i.e. branch groups can be nested and branches are selected with branch() and $defaultBranch. 