<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WebsocketServerCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lowendping:websocket';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Start the LowEndPing Ratchet Websocket server.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire() {
		$loop   = React\EventLoop\Factory::create();
		$pusher = new LowEndPing\Pusher;
		
		$zmq_host = Config::get('lowendping.websocket.zeromq.host', '127.0.0.1') . ':' . Config::get('lowendping.websocket.zeromq.port', 5555);
	
		// Listen for the web server to make a ZeroMQ push after a response
		$context = new React\ZMQ\Context($loop);
		$pull = $context->getSocket(ZMQ::SOCKET_PULL);
		
		$this->info('Binding ZMQ to ' . $zmq_host);
		$pull->bind('tcp://' . $zmq_host); // Binding to 127.0.0.1 means the only client that can connect is itself
		$pull->on('message', array($pusher, 'onServerResponse'));
	
		$this->info('Binding Ratchet Websocket to 0.0.0.0:' . Config::get('lowendping.websocket.port', 8080));
		// Set up our WebSocket server for clients wanting real-time updates
		$webSock = new React\Socket\Server($loop);
		$webSock->listen(Config::get('lowendping.websocket.port', 8080), '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
		$webServer = new Ratchet\Server\IoServer(
			new Ratchet\Http\HttpServer(
				new Ratchet\WebSocket\WsServer(
					new Ratchet\Wamp\WampServer(
						$pusher
					)
				)
			),
			$webSock
		);
	
		$loop->run();
	}

}
