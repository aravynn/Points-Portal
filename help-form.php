<?php

/**
 *
 * Order Form Page
 * load via INCLUDE at page load.
 *
 */
 
 if(count(get_included_files()) ==1){
 	 http_response_code(403);
 	 exit("ERROR 403: Direct access not permitted.");
}

?>
<div id="help">
	<h2>Website Assistance</h2>
	<p> Please use the form below to contact administrators for assistance. Select Ordering help for more information or concerns with products and ordering. For website and technical issues, use Technical Issue. </p>
	<?php
		if(isset($_GET['r'])){
			if($_GET['r'] == 'success'){
				echo '<em class="success">Email Successfully sent.</em>';	
			}
			if($_GET['r'] == 'failure'){
				echo '<em class="error">ERROR: Email failed to send, please contact technical support.</em>';	
			}
		}
	?>

	<form method="post" action="authenticate.php">
		<label for="messagetype">Contact Type: </label><select name="messagetype">
			<option value="Admin">Ordering Help</option>
			<option value="Tech">Technical Issue</option>	
		</select><br/>
		<label for="subject">Subject: </label><input type="text" id="subject" name="subject" /><br />
		<label id="messagelabel" for="message">Message: </label><textarea name="message"></textarea><br />
		<input type="hidden" name="action" value="SendHelpEmail" />
		<input type="submit" />
	</form>

</div>