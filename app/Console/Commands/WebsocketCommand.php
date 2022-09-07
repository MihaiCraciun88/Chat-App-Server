<?php
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

// Your shell script
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;

class WebsocketCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'websocket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve websocket';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loop   = \React\EventLoop\Loop::get();
        $pusher = new \App\Models\Pusher;

        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new \React\Socket\SocketServer(env('WEBSOCKER_ADDRESS', '127.0.0.1:8080'), [], $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Ratchet\Wamp\WampServer(
                        $pusher
                    )
                )
            ),
            $webSock
        );
    
        $loop->run();
    }
}