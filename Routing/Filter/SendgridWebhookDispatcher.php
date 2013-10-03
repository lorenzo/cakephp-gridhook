<?php

App::uses('DispatcherFilter', 'Routing');
App::uses('SendgridEvent', 'GridHook.Model');
App::uses('CakeEvent', 'Event');

/**
 * A middleware to process Sendgrid webhook events
 *
 */
class SendgridWebhookDispatcher extends DispatcherFilter {

/**
 * The priority on which this filter should run
 * We want it to run before routing.
 *
 * @var integer
 */
	public $priority = 9;

/**
 * Inspects the requests and if the url is the one set for processing the Sendgrid
 * webhook, it will convert the payload to one or several SendgridEvent objects and
 * trigger the configured callback for each one of them.
 *
 * @param CakeEvent $event
 * @return CakeResponse
 */
	public function beforeDispatch(CakeEvent $event) {
		$config = Configure::read('Dispatcher.filters.GridHook');
		$endpoint = '/webhook/sendgrid';

		if (!empty($config['endpoint'])) {
			$endpoint = $config['endpoint'];
		}

		$request = $event->data['request'];
		$response = $event->data['response'];

		if ('/' . $request->url !== $endpoint || !$request->is('post')) {
			return;
		}

		$callable = $config['handler'];
		if (!is_callable($callable)) {
			throw new InvalidArgumentException('Not a valid handler for sendgrid webhooks');
		}

		$this->_trigger($callable, json_decode($request->input(), true));

		$response->statusCode(200);
		$event->stopPropagation();
		return $response;
	}

/**
 * Triggers the callable by passing a new SendgridEvent object
 *
 * @param callable $callable
 * @param array $events
 * @return void
 */
	protected function _trigger($callable, $events) {
		$callable($events);
	}

}
