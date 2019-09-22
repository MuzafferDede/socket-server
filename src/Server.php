<?php

namespace Nemrut;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use Illuminate\Support\Facades\Request;
use React\Socket\Server as ReactServer;

class Server
{

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Running HTTP Server
     */
    public function run()
    {
        $loop = Factory::create();
        $socket = new ReactServer("$this->host:$this->port", $loop);

        $socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                $data = trim($data);
                if ($data != "") {
                    $user = Request::create('/', 'GET', ["user" => $data]);
                    $user = app()->handle($user);
                    if ($data == 1) {
                        $connection->close();
                    }
                    $connection->write($user->content() . "\n");
                }
            });
        });

        echo "Listening on {$socket->getAddress()}\n";

        $loop->run();
    }
}
