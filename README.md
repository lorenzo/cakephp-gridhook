# Webhook handler for SendGrid events#

This plugin provides a dispatcher filter that will listen to any request send from SendGrid as a webhook and fire
any configured callback with the decoded data as it was received.

This is useful for storing or calculating your own statistics about emails you send, to unsubscribe users when emails
bounce or when marked as spam.

## Requirements ##

* CakePHP 2.x
* PHP 5.3+
* Composer

## Installation ##

The only installation method supported by this plugin is by using composer. Just add this to your composer.json configuration:

	{
	  "extra": {
		"installer-paths": {
				"app/Plugin/GridHook": ["lorenzo/cakephp-gridhook"]
		}
	  },
	  "require" : {
		"lorenzo/cakephp-gridhook": "master"
	  }
	}

### Enable plugin

You need to enable the plugin your `app/Config/bootstrap.php` file:

    CakePlugin::load('GridHook');

Additionally, in the same file, add this to your dispatcher filters array:

	Configure::write('Dispatcher.filters', array(
		// ... Other filters ...
		'GridHook' => array(
			'callable' => 'GridHook.SendgridWebhookDispatcher',
			'handler' => 'MyHandlerClass::aMethod' // Configure this value at will
		)
	));

The `handler` key is any valid callable object or closure that will be called each time an event is received
from SendGrid. The `handler` key is mandatory for this plugin to work correctly. Another example:

	Configure::write('Dispatcher.filters', array(
		// ... Other filters ...
		'GridHook' => array(
			'callable' => 'GridHook.SendgridWebhookDispatcher',
			'handler' => function($sendGridEvent) {
				// Do some stuff
			}
		)
	));

### Configuring listener url

By default this plugin will listen on the `/webhook/sendgrid` url, if for any reason you want to change it,
set the `endpoint` key to another path

	Configure::write('Dispatcher.filters', array(
		// ... Other filters ...
		'GridHook' => array(
			'callable' => 'GridHook.SendgridWebhookDispatcher',
			'handler' => 'MyHandlerClass::aMethod' // Configure this value at will,
			'endpoint' => '/sendgrid-hook'
		)
	));

## Handling an event sent from SendGrid

The callback configured in the `handler` key will be called for each event generated from sendgrid
that was received via the webhook. The first argument of this function will be an object of type
`SendGridEvent` that will contain call the properties sent for the event. This is an example:

	App::uses('SendGridEvent', 'GridHook.Model');

	class Newsletter extends AppModel {

		public static function handleEvent(SendgidEvent $event) {
			if ($event->isSpamReport()) {
				ClassRegistry::init('User')->usubscribe($event->email);
			}
		}
	}

