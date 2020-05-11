<?php
/*
Plugin Name: OneSignal Push To WordPress Users
Description: Allows to send OneSignal push notifications to chosen WordPress users only. To be used with WP-AppKit mobile apps.
Version:     1.0.0
Author:      Uncategorized Creations
Author URI:  http://getwpappkit.com
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

This plugin, like WordPress, is licensed under the GPL.
Use it to make something cool, have fun, and share what you've learned with others.
*/

namespace OneSignalPushToWpUsersPlugin;

/**
 * SET YOUR OWN ONESIGNAL APP ID AND AUTH KEY HERE
 */
define('ONESIGNAL_APP_ID', "a4abe74f-2a7e-4295-932f-8c72b308dfbc");
define('ONESIGNAL_AUTH_KEY', "NDcxNjZlMDYtOTYyMi00Y2I5LWI4YmUtZTU3YjE5OTZlZGJm");

/**
 * Add the Back Office panel where the users will be selected and the notifications sent:
 */
add_action( 'admin_menu', __NAMESPACE__ ."\add_admin_page" );
function add_admin_page() {
	add_management_page(
	        'OneSignal push to WP Users',
	        'OneSignal push to WP Users',
	        'manage_options',
	        'onesignal-push-to-wp-users-plugin',
	        __NAMESPACE__ ."\admin_page_content"
	);
}

/**
 * Render the Back Office panel: display a form where we can choose the WordPress users to
 * send notifications to, and handle notification sending on form submission.
 */
function admin_page_content() {

	$notification_result = [
		'sent' => false,
		'error' => '',
	];

	//If user choice form is submitted, send notification to chosen users only:
	if ( !empty($_POST['user_ids']) && is_array($_POST['user_ids']) ) {
		//Make sure that sent ids are integers:
		$user_ids = array_map('intval', $_POST['user_ids']);
		//Send notification:
		$response = send_notification($user_ids);
		//Handle notification result:
		$notification_result['sent'] = true;
		if ( is_wp_error($response) ) {
            $notification_result['error'] = "Error: ". $response->get_error_message;
        } else if ( !is_array($response) || !isset($response['body']) ) {
			$notification_result['error'] = "Error: ". json_encode($response);
		}
	}

	//Retrieve all WordPress users to display them in a checkbox list:
	$users = get_users();

	?>
	<div class="wrap">

		<?php //If form was submitted, display a success or error message: ?>
		<?php if( $notification_result['sent'] ): ?>
			<div id="message" class="updated notice notice-<?php echo !empty($notification_result['error']) ? 'error' : 'success'; ?>">
				<?php $user_logins = wp_list_pluck(get_users(['include'=>$user_ids]),'user_login'); ?>
				<p><?php echo !empty($notification_result['error']) ? $notification_result['error'] : 'Notification sent to '. implode(', ', $user_logins) .' !'; ?></p>
			</div>
		<?php endif; ?>

		<h2>Onesignal User</h2>

		<?php //Display users choice form: one checkbox per user: ?>
		<form method="post">
			<label>Send notification to users: </label><br>
			<?php foreach($users as $user): ?>
				<input type="checkbox" name="user_ids[]" value="<?php echo $user->ID; ?>" id="user-<?php echo $user->ID; ?>">
				<label for="user-<?php echo $user->ID; ?>"><?php echo $user->user_login; ?></label><br>
			<?php endforeach; ?>
			<input type="submit" value="Send notification!"/>
		</form>
	</div>
	<?php
}

/**
 * Send notification using OneSignal Rest API (See https://documentation.onesignal.com/reference)
 */
function send_notification( $user_ids ) {

	if ( !is_array($user_ids) || empty($user_ids) ) {
		return new WP_Error( 'wrong-user-ids', 'Wrong user ids' );
	}

	$onesignal_post_url = 'https://onesignal.com/api/v1/notifications';

	$notification_title = "Hey from WordPress!";
	$notification_content = "It is ". date('H:i:s') .": a good time to enjoy your WP-AppKit app :)";

	//See https://documentation.onesignal.com/reference#create-notification for available notification fields and config.
	//We use include_external_user_ids to send WP user ids:
	$fields = array(
		'app_id' => ONESIGNAL_APP_ID,
		'headings' => ['en' => $notification_title],
		'include_external_user_ids' => $user_ids, //This is where we pass our chosen users, using OneSignal external_user ids
		'isAnyWeb' => true,
		'contents' => ['en' => $notification_content],
	);

	//Set OneSignal request params:
	$request = array(
	    'headers' => array(
	        'content-type' => 'application/json;charset=utf-8',
	        'Authorization' => 'Basic '. ONESIGNAL_AUTH_KEY,
		),
		'body' => wp_json_encode($fields),
		'timeout' => 3,
	);

	//Send notification!
	$response = wp_remote_post($onesignal_post_url, $request);

	return $response;
}
