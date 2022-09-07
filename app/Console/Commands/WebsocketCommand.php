<?php
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

// Your shell script
use React\EventLoop\Loop;
use App\Services\PusherService;
use React\Socket\SocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;

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
        $loop   = Loop::get();
        $pusher = new PusherService;

        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new SocketServer(env('WEBSOCKER_ADDRESS', '127.0.0.1:8080'), [], $loop);
        $webServer = new IoServer(
            new HttpServer(
                new WsServer(
                    new WampServer(
                        $pusher
                    )
                )
            ),
            $webSock
        );
    
        $loop->run();
    }
}