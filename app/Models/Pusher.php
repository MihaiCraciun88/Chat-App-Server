<?php
namespace App\Models;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
        $user = $this->getUser($topic);
        foreach ($user->messages as $message) {
            $topic->broadcast([
                'wsChatId' => $user->password,
                'message'  => $message->message,
            ]);
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {

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
        $pusherId = env('PUSHER_ADMIN_KEY');
        $data = json_decode($event, true);
        $topic->broadcast($data);
        echo "broadcast\n";

        $user = $this->getUser($topic);
        $message = Message::create([
            'user_id' => $user->id,
            'own'     => $topic === $data['wsChatId'],
            'message' => $data['message'],
        ]);

        print_r([$user, $message, $data]);

        if (isset($this->subscribedTopics[$pusherId]) && $data['wsChatId'] !== $pusherId) {
            $this->subscribedTopics[$pusherId]->broadcast($data);
        }
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {

    }

    public function getUser($topic) {
        try {
            $user = User::where('password', $topic)->firstOrFail();
        } catch (\Exception $e) {
            $user = User::create([
                'name'      => '',
                'password'  => $topic,
            ]);
        }
        return $user;
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!isset($this->subscribedTopics[$entryData['category']])) {
            return;
        }

        $topic = $this->subscribedTopics[$entryData['category']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
    }
}