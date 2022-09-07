<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $adminUuid = env('WEBSOCKER_ADMIN_ID');
        if ($adminUuid === $topic->getId()) {
            $messages = Message::whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])->get();
        } else {
            $this->subscribedTopics[$topic->getId()] = $topic;
            $user = $this->getUser($topic);
            $messages = $user->messages;
        }
        foreach ($messages as $message) {
            $topic->broadcast([
                'uuid'  => $user->uuid,
                'date'  => $message->created_at->format('H:i:s'),
                'text'  => $message->message,
            ]);
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {}
    public function onOpen(ConnectionInterface $conn) {}
    public function onClose(ConnectionInterface $conn) {
        dump($conn);
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $adminUuid = env('WEBSOCKER_ADMIN_ID');
        $data = json_decode($event, true);

        $user = $this->getUser($topic);
        try {
            $oldMessage = $user->messages()->first();
            $conversation_id = $oldMessage->conversation_id;
        } catch (\Exception $e) {
            $conversation = Conversation::factory()->create();
            $conversation_id = $conversation->id;
            DB::table('conversation_user')->insert([
                'conversation_id'   => $conversation_id,
                'user_id'           => $user->id,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
        $message = Message::create([
            'user_id'           => $user->id,
            'conversation_id'   => $conversation_id,
            'message'           => $data['message'],
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        if (isset($this->subscribedTopics[$adminUuid]) && $data['uuid'] !== $adminUuid) {
            $this->subscribedTopics[$adminUuid]->broadcast($data);
        }
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        dump($e);
    }

    public function getUser($topic) {
        try {
            $user = User::where('uuid', $topic)->firstOrFail();
        } catch (\Exception $e) {
            $user = User::create([
                'name'      => '',
                'uuid'      => $topic,
                'password'  => '',
            ]);
        }
        return $user;
    }
}