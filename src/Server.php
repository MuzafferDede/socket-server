<?php

namespace Nemrut;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer as ReactServer;

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

    /**
     * @var string
     */
    protected $path;

    public function __construct($host, $port, $path)
    {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
    }

    /**
     * Running HTTP Server
     */
    public function run()
    {
        $loop = Factory::create();
        $socket = new ReactServer("$this->host:$this->port", $loop);

        $pool = new Pool($this->path);

        $socket->on('connection', function (ConnectionInterface $connection) use ($pool) {
            $pool->add($connection);
        });

        echo "Listening on {$socket->getAddress()}\n";

        $loop->run();
    }
}
