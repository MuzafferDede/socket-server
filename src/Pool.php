<?php

namespace Nemrut;

use SplObjectStorage;
use Illuminate\Http\Request;
use React\Socket\ConnectionInterface;
use Illuminate\Foundation\Http\Kernel;

class Pool
{
    private $connections;

    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    public function add(ConnectionInterface $connection)
    {
        $this->response('Connected to socket server. Now login the device', $connection);

        $this->setConnectionToken($connection, null);

        $connection->on('data', function ($data) use ($connection) {
            $this->handleRequest($data, $connection);
        });

        $connection->on('close', function () use ($connection) {
            $data = [
                'action' => '001',
                'offline' => true
            ];
            $this->handleRequest(json_encode($data, true), $connection);
            $this->connections->offsetUnset($connection);
            echo $connection->getRemoteAddress() . " disconnected. Connected Devices: " . $this->connections->count() . PHP_EOL;
        });
    }

    private function handleRequest($data, ConnectionInterface $connection)
    {
        if ($params = $this->initRequest($data, $connection)) {

            $app = require app()->basePath() . '/bootstrap/app.php';
            $kernel = $app->make(Kernel::class);

            $request = Request::create('/api', 'POST', $params);
            $response =  $kernel->handle($request);

            if (!empty($response)) {
                $response = json_decode($response->getContent());

                $clients = $response->data->devices ?? [$this->getConnectionToken($connection)];
                if (isset($response->data->devices)) {
                    unset($response->data->devices);
                }
                $this->sendDataTo($response, $clients);
            }

            $kernel->terminate($request, $response);
        }
    }

    private function initRequest($request, ConnectionInterface $connection)
    {
        $request = json_decode($request, true);
        $token = $request['api_token'] ?? $this->getConnectionToken($connection) ?? null;

        if (empty($token)) {
            $this->response('Device is not logged in', $connection, false);
            return false;
        }

        if (!$request) {
            $this->response('JSON data format is wrong', $connection, false);
            return false;
        }

        if (isset($request['api_token'])) {
            $this->setConnectionToken($connection, $token);
            echo $connection->getRemoteAddress() . " connected. Connected Devices: " . $this->connections->count() . PHP_EOL;
        }

        $request['api_token'] = $token;

        return $request;
    }

    private function sendDataTo($data, $clients)
    {
        foreach ($this->connections as $connection) {
            $token = $this->getConnectionToken($connection);
            $tracker = $data->data->tracker_id ?? null;
            if (in_array($token, $clients) || $token == $tracker) {
                $connection->write(json_encode($data, true) . PHP_EOL);
            }
        }
    }

    private function response($message, ConnectionInterface $connection, $status = true)
    {
        $connection->write(json_encode(['success' => $status, 'message' => $message]) . PHP_EOL);
    }

    private function getConnectionToken(ConnectionInterface $connection)
    {
        return $this->connections->offsetGet($connection);
    }

    private function setConnectionToken(ConnectionInterface $connection, $token)
    {
        $this->connections->offsetSet($connection, $token);
    }
}
