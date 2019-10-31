<?php

namespace Nemrut;

use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;

class Client
{
    protected $action;
    protected $api_token;
    public function __construct($action, $api_token)
    {
        $this->action = $action;
        $this->api_token = $api_token;
    }

    /**
     * Running HTTP Server
     */
    public function run()
    {
        $loop =  Factory::create();
        $connector = new Connector($loop);
        $connector->connect(env('REMOTE_URL') . ':9000')
            ->then(function (ConnectionInterface $connection) {
                $connection->write(json_encode(['action' => $this->action, 'api_token' => $this->api_token], true) . PHP_EOL);

                $connection->on('data', function ($data) use ($connection) {
                    if ($data->action) {
                        $request = Request::create('/api', 'POST', $data);

                        $request->headers->set('Accept',  "application/json");

                        $response =  app()->handle($request);
                        if (!empty($response)) {
                            $connection->write($response->getContent() . PHP_EOL);
                        }
                    }
                });
            }, function (Exception $e) {
                info($e->getMessage());
            });
        $loop->run();
    }
}
