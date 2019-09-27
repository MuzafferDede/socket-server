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

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $params;

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

        $socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                $data = trim(strtolower($data));

                if (!($this->params = json_decode($data, true))) {
                    $connection->close();
                }
                try {
                    $request = Request::create($this->path, 'POST', $this->params);
                    $response =  app()->handle($request);
                    $connection->write($response->getContent());
                } catch (Exception $e) {
                    $connection->write('Caught exception: ',  $e->getMessage(), "\n");
                    $connection->close();
                }
            });
        });

        echo "Listening on {$socket->getAddress()}\n";

        $loop->run();
    }
}
