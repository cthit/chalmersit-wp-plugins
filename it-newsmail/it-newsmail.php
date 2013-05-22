<?php
/* 
	Plugin Name: IT Newsmail
	Plugin URI: https://chalmers.it
	Description: Newsposts are mailed to users automatically 
	Author: Max Witt
	License: MIT
*/

global $wpdb;
define("IT_NEWSMAIL_TABLE", $wpdb->prefix. "newsmail");

register_activation_hook(__FILE__, 'it_newsmail_activate');
register_deactivation_hook(__FILE__,'it_newsmail_deactivate');

function it_newsmail($post_id){
	
	// Get all users who opt in for email

}

function it_newsmail_activate(){
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "CREATE TABLE IF NOT EXISTS ".IT_NEWSMAIL_TABLE." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) unsigned NOT NULL,
		cat_id bigint(20) signed NOT NULL,

		PRIMARY KEY (id)
		);";
	
	dbDelta($sql);
}

function it_newsmail_deactivate(){
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "DROP TABLE ".IT_NEWSMAIL_TABLE.";";
	dbDelta($sql);
}


function itnm_setvalues(){
	error_log("itnm_setvalues was called!");
}

function it_newsmail_form(){

?>
<br class="clear" />
<form id="your-profile" name="itnm_setvalues" action="<?php echo plugins_url(__FILE__); ?>" method="post">
	<h2>Mailutskick för nyheter</h2>
	<table class="form-table">
		<tr>
			<th><label for="itnm_all">Alla nyheter</label></th>
			<td><input type="checkbox" id="itnm_-1" value=""/></td>
		</tr>
	</table>
	<p class="submit">
		<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($current_user->ID); ?>" />
		<input type="submit" class="large" value="Spara mailinställningar" name="submit" />
</form>
<?php
}

?>