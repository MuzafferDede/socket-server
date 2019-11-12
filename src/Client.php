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
                cache()->forget('online');
                cache()->rememberForever('online', function () {
                    return true;
                });
                $connection->write(json_encode(['action' => $this->action, 'api_token' => $this->api_token], true) . PHP_EOL);
                $connection->on('data', function ($params) use ($connection) {
                    if ($params->action) {
                        $app = require app()->basePath() . '/bootstrap/app.php';
                        $kernel = $app->make(Kernel::class);

                        $request = Request::create('/api', 'POST', $params);
                        $request->headers->set('Accept',  "application/json");

                        $response =  $kernel->handle($request);
                        if (!empty($response)) {
                            $connection->write($response->getContent() . PHP_EOL);
                        }
                        $kernel->terminate($request, $response);
                    }
                });
                $connection->on('close', function () {
                    cache()->forget('online');
                    cache()->rememberForever('online', function () {
                        return false;
                    });
                });
            }, function (Exception $e) {
                info($e->getMessage());
            });
        $loop->run();
    }
}
