<?php

namespace Nemrut;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer as ReactServer;

class Server
{

    /**
     * Running HTTP Server
     */
    public function run()
    {
        $loop = Factory::create();
        $socket = new ReactServer('0.0.0.0:9000', $loop);

        $pool = new Pool();

        $socket->on('connection', function (ConnectionInterface $connection) use ($pool) {
            $pool->add($connection);
        });

        echo "Listening on {$socket->getAddress()}\n";

        $loop->run();
    }
}
