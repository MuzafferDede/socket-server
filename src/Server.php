<?php

namespace Nemrut;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer as ReactServer;

class Server
{
    private $port;

    function __construct($port)
    {
        $this->port = $port;
    }

    /**
     * Running HTTP Server
     */
    public function run()
    {
        $loop = Factory::create();
        $socket = new ReactServer('0.0.0.0:' . $this->port, $loop);

        $pool = new Pool();

        $socket->on('connection', function (ConnectionInterface $connection) use ($pool) {
            $pool->add($connection);
        });

        echo date('Y-m-d H:s') . ' ' . "Listening on {$socket->getAddress()}\n";

        $loop->run();
    }
}
