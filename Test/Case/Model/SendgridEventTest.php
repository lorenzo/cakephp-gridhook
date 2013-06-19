<?php

App::uses('SendgridEvent', 'GridHook.Model');

class SendgridEventTest extends CakeTestCase {

/**
 * Tests set() method
 *
 * @return void
 */
	public function testSet() {
		$date = new DateTime('1 minute ago');
		$data = array(
			'event' => 'open',
			'email' => 'foo@bar.com',
			'timestamp' => $date->format('U'),
			'category' => 'my category'
		);
		$event = new SendgridEvent;
		$event->set($data);

		$this->assertEquals('open', $event->type);
		$this->assertEquals('foo@bar.com', $event->email);
		$this->assertEquals($date, $event->timestamp);
		$this->assertEquals('my category', $event->category);

		$date = new DateTime();
		$data = array(
			'event' => 'open',
			'email' => 'foo@bar.com'
		);
		$event = new SendgridEvent;
		$event->set($data);
		$this->assertEquals($date, $event->timestamp);
	}

/**
 * Provider for all the type of events
 *
 * @return array
 */
	public function typesProvider() {
		return array(
			array('processed'),
			array('deferred'),
			array('dropped'),
			array('delivered'),
			array('open'),
			array('click'),
			array('bounce'),
			array('spamreport'),
			array('unsubscribe'),
		);
	}

/**
 * Tests the type detector methods
 *
 * @dataProvider typesProvider
 * @return void
 */
	public function testDetectors($type) {
		$event = new SendgridEvent;
		$event->set(array('event' => $type, 'email' => 'foo@bar.com'));
		$this->assertTrue($event->{'is' . ucFirst($type)}());
		$event->type = 'foo';
		$this->assertFalse($event->{'is' . ucFirst($type)}());
	}

}
