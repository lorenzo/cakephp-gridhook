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

		if (env('Content-Type') === 'application/json') {
			$this->_parseBatch($request, $callable);
		} else {
			$this->_trigger($callable, $request->data);
		}

		$event->stopPropagation();
		return $response;
	}

/**
 * Processes a batch event list and triggers the callback for each event
 * correctly parsed
 *
 * @param CakeRequest $request
 * @param callable $callable
 * @return void
 */
	protected function _parseBatch($request, $callable) {
		$batch = explode("\r\n", $request->input());
		foreach ($batch as $document) {
			$this->_trigger($callable, json_decode($document, true));
		}
	}

/**
 * Triggers the callable by passing a new SendgridEvent object
 *
 * @param callable $callable
 * @param array $document
 * @return void
 */
	protected function _trigger($callable, $document) {
		$event = new SendgridEvent();
		$event->set($document);
		$callable($event);
	}

}
