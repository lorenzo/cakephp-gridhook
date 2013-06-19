<?php

App::uses('SendgridWebhookDispatcher', 'GridHook.Routing/Filter');
App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');

class SendgridWebhookDispatcherTest extends CakeTestCase {

/**
 * Tests dispatching the callback for a single event
 *
 * @return void
 */
	public function testHandleHookSingle() {
		$dispatcher = new SendgridWebhookDispatcher;
		$request = $this->getMock('CakeRequest', array('is'));
		$response = new CakeResponse;
		$callback = $this->getMock('stdClass', array('__invoke'));

		Configure::write('Dispatcher.filters.GridHook', array(
			'handler' => $callback
		));
		$data = array(
			'event' => 'open',
			'email' => 'foo@bar.com'
		);
		$builtEvent = new SendgridEvent;
		$builtEvent->set($data);

		$callback->expects($this->once())->method('__invoke')->with($builtEvent);
		$request->expects($this->once())->method('is')->with('post')->will($this->returnValue(true));
		$request->url = 'webhook/sendgrid';
		$request->data = $data;

		$event = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response'));
		$this->assertSame($response, $dispatcher->beforeDispatch($event));
		$this->assertEquals(200, $response->statusCode());
		$this->assertTrue($event->isStopped());
	}

/**
 * Tests dispatcher when urls do not match
 *
 * @return void
 */
	public function testNoMatchingURL() {
		$dispatcher = new SendgridWebhookDispatcher;
		$request = $this->getMock('CakeRequest', array('is'));
		$response = new CakeResponse;

		$request->expects($this->any())->method('is')->with('post')->will($this->returnValue(true));
		$request->url = 'other/thing';
		$event = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response'));
		$this->assertNull($dispatcher->beforeDispatch($event));
		$this->assertFalse($event->isStopped());
	}

/**
 * Tests dispatcher when urls do not match
 *
 * @return void
 */
	public function testMatchingURLNotPost() {
		$dispatcher = new SendgridWebhookDispatcher;
		$request = $this->getMock('CakeRequest', array('is'));
		$response = new CakeResponse;

		$request->expects($this->once())->method('is')->with('post')->will($this->returnValue(false));
		$request->url = 'webhook/sendgrid';
		$event = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response'));
		$this->assertNull($dispatcher->beforeDispatch($event));
		$this->assertFalse($event->isStopped());
	}

/**
 * Tests dispatching the callback for a single event using a custom endpoint
 *
 * @return void
 */
	public function testHandleHookSingleCustomURL() {
		$dispatcher = new SendgridWebhookDispatcher;
		$request = $this->getMock('CakeRequest', array('is'));
		$response = new CakeResponse;
		$callback = $this->getMock('stdClass', array('__invoke'));

		Configure::write('Dispatcher.filters.GridHook', array(
			'handler' => $callback,
			'endpoint' => '/hook'
		));
		$data = array(
			'event' => 'open',
			'email' => 'foo@bar.com'
		);
		$builtEvent = new SendgridEvent;
		$builtEvent->set($data);

		$callback->expects($this->once())->method('__invoke')->with($builtEvent);
		$request->expects($this->once())->method('is')->with('post')->will($this->returnValue(true));
		$request->url = 'hook';
		$request->data = $data;

		$event = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response'));
		$this->assertSame($response, $dispatcher->beforeDispatch($event));
		$this->assertEquals(200, $response->statusCode());
		$this->assertTrue($event->isStopped());
	}

/**
 * Tests that passing an incorrect callback will throw an exception
 *
 * @expectedException InvalidArgumentException
 * @return void
 */
	public function testInvalidHandler() {
		$dispatcher = new SendgridWebhookDispatcher;
		$request = $this->getMock('CakeRequest', array('is'));
		$response = new CakeResponse;

		Configure::write('Dispatcher.filters.GridHook', array(
			'handler' => new stdClass,
		));
		$data = array(
			'event' => 'open',
			'email' => 'foo@bar.com'
		);
		$builtEvent = new SendgridEvent;
		$builtEvent->set($data);

		$request->expects($this->once())->method('is')->with('post')->will($this->returnValue(true));
		$request->url = 'webhook/sendgrid';
		$event = new CakeEvent('Dispatcher.beforeDispatch', $this, compact('request', 'response'));
		$dispatcher->beforeDispatch($event);
	}
}
