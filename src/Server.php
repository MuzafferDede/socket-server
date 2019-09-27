<?php

namespace Nemrut;

use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
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
            $connection->on('data', function ($request) use ($connection) {
                $request = trim(strtolower($request));

                if (!($this->params = json_decode($request, true))) {
                    $connection->close();
                }

                $myrequest = new \Illuminate\Http\Request;
                $myrequest = $myrequest::create($this->path, 'POST', $this->params);

                $response =  app()->handle($myrequest);
                $content = $response->getContent();
                $connection->write($content);
            });
        });

        echo "Listening on {$socket->getAddress()}\n";

        $loop->run();
    }
}
