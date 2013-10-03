<?php

/**
 * Represents an event received by the webhook
 *
 * @see http://sendgrid.com/docs/API_Reference/Webhooks/event.html
 */
class SendgridEvent implements JsonSerializable {

/**
 * The type of event (processed, dropped, delivered, bounce, open, click, unsubscribe, spamreport)
 *
 * @var string
 */
	public $event;

/**
 * The email recipient to which this event is related
 *
 * @var string
 */
	public $email;

/**
 * When the webhook event was received
 *
 * @var DateTime
 */
	public $timestamp;

/**
 * The category that was assigned to the delivered email
 *
 * @var string
 */
	public $category;

/**
 * All params received from the webhook
 *
 * @var array
 */
	public $data = array();

/**
 * All properties
 *
 * @var array
 */
	public $properties = [];

/**
 * Called when marshalled back to an object
 *
 * @param array $properties
 */
	public function __construct(array $properties) {
		$this->set($properties);
	}

/**
 * Sets the properties in this object as received from the webhook
 *
 * @return void
 */
	public function set($properties) {
		$this->properties = $properties;

		if (isset($properties['event'])) {
			$this->event = $properties['event'];
		}

		if (isset($properties['email'])) {
			$this->email = $properties['email'];
		}

		if (isset($properties['timestamp'])) {
			if (is_numeric($properties['timestamp'])) {
				$this->timestamp = new DateTime('@' . $properties['timestamp']);
			} elseif (is_array($properties['timestamp'])) {
				$this->timestamp = new DateTime($properties['timestamp']['date'], new DateTimezone($properties['timestamp']['timezone']));
			}
		} else {
			$this->timestamp = new DateTime('now');
		}

		$this->timestamp->setTimezone(new DateTimezone('UTC'));

		if (isset($properties['category'])) {
			$this->category = $properties['category'];
		}

		if (!empty($properties['data'])) {
			$this->data = $properties['data'];
		}
	}

/**
 * See http://php.net/manual/en/jsonserializable.jsonserialize.php
 *
 * @return array
 */
	public function jsonSerialize() {
		return $this->properties;
	}

/**
 * Whether the email was processed by sendgird
 *
 * @return boolean
 */
	public function isProcessed() {
		return $this->event === 'processed';
	}

/**
 * Whether the email was deferred by sendgird
 *
 * @return boolean
 */
	public function isDeferred() {
		return $this->event === 'deferred';
	}

/**
 * Whether the email was dropped by sendgird
 *
 * @return boolean
 */
	public function isDropped() {
		return $this->event === 'dropped';
	}

/**
 * Whether the email was delivered by sendgird
 *
 * @return boolean
 */
	public function isDelivered() {
		return $this->event === 'delivered';
	}

/**
 * Whether the email was opened by the recipient
 *
 * @return boolean
 */
	public function isOpen() {
		return $this->event === 'open';
	}

/**
 * Whether an url in the email was clicked by the recipient
 *
 * @return boolean
 */
	public function isClick() {
		return $this->event === 'click';
	}

/**
 * Whether the email bounced
 *
 * @return boolean
 */
	public function isBounce() {
		return $this->event === 'bounce';
	}

/**
 * Whether the email was marked as spam by the recipient
 *
 * @return boolean
 */
	public function isSpamReport() {
		return $this->event === 'spamreport';
	}

/**
 * Whether the recipient unsubscribed from the newsletter
 *
 * @return boolean
 */
	public function isUnsubscribe() {
		return $this->event === 'unsubscribe';
	}
}
