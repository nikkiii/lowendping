<?php namespace LowEndPing;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
	/**
	 * A lookup of all the topics clients have subscribed to
	 */
	protected $subscribedTopics = array();

	public function onSubscribe(ConnectionInterface $conn, $topic) {
		// When a visitor subscribes to a topic link the Topic object in a  lookup array
		if (!array_key_exists($topic->getId(), $this->subscribedTopics)) {
			$this->subscribedTopics[$topic->getId()] = $topic;
		}
	}
	
	public function onUnSubscribe(ConnectionInterface $conn, $topic) {
		if (array_key_exists($topic->getId(), $this->subscribedTopics)) {
			unset($this->subscribedTopics[$topic->getId()]);
		}
	}
	
	public function onOpen(ConnectionInterface $conn) {
	}
	
	public function onClose(ConnectionInterface $conn) {
	}
	
	public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
		// In this application if clients send data it's because the user hacked around in console
		$conn->callError($id, $topic, 'You are not allowed to make calls')->close();
	}
	
	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
		// In this application if clients send data it's because the user hacked around in console
		$conn->close();
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e) {
	}

	/**
	 * @param string JSON'ified string we'll receive from ZeroMQ
	 */
	public function onServerResponse($entry) {
		$entryData = json_decode($entry, true);

		// If the lookup topic object isn't set there is no one to publish to
		if (!array_key_exists($entryData['query_id'], $this->subscribedTopics)) {
			return;
		}
		
		$response = \QueryResponse::where('query_id', $entryData['query_id'])->where('server_id', $entryData['server_id']);
		
		if ($response) {
			if (\Config::get('lowendping.archive.enabled', false)) {
				$response->sent = 1;
				$response->save();
			} else {
				$response->delete();
			}
		}

		$topic = $this->subscribedTopics[$entryData['query_id']];

		// re-send the data to all the clients subscribed to that category
		$topic->broadcast($entryData);
	}
}