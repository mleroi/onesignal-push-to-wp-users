# onesignal-push-to-wp-users

WordPress micro plugin that implements an example of how to send OneSignal push notifications to chosen WordPress users only.
To be used with WP-AppKit mobile apps and PWAs.

This plugin should be seen more as a very basic demo from which you can extract ideas to implement your own features.

*Prerequisite*

This plugin is designed to send notifications to an existing WP-AppKit app that is already configured to work with OneSignal.
See [this tutorial on WP-AppKit website](https://uncategorized-creations.com/4905/send-push-notifications-to-wordpress-users/) to learn how to create such an app with WP-AppKit.

*Setup*

- Set your OneSignal **App ID** and **Auth key** in _onesignal-push-to-wp-users.php_ (constants ONESIGNAL_APP_ID and ONESIGNAL_AUTH_KEY).
- You can also set your own notification message in the "send_notification()" function
- Then go to the "OneSignal > WP Users" admin page under the "Tools" menu, select the WordPress users you want to send notifications to and finally send push notifications that only the selected WordPress users will receive in their app!
