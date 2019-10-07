<?php

namespace Nemrut;

use SplObjectStorage;
use React\Socket\ConnectionInterface;
use Symfony\Component\HttpFoundation\Request;

class Pool
{
    private $connections;

    private $path;

    public function __construct($path)
    {
        $this->connections = new SplObjectStorage();
        $this->path = $path;
    }

    public function add(ConnectionInterface $connection)
    {
        echo $connection->getRemoteAddress() . " connected" . PHP_EOL;

        $this->response('Connected', $connection);

        $this->setConnectionToken($connection, null);

        $connection->on('data', function ($data) use ($connection) {
            $this->handleRequest($data, $connection);
        });

        $connection->on('close', function () use ($connection) {
            $this->connections->offsetUnset($connection);
            echo $connection->getRemoteAddress() . " disconnected" . PHP_EOL;
        });
    }

    private function handleRequest($data, ConnectionInterface $connection)
    {
        if ($params = $this->initRequest($data, $connection)) {
            $request = Request::create($this->path, 'POST', $params);

            $request->headers->set('Accept',  "application/json");

            $response =  app()->handle($request);
            $response = json_decode($response->getContent());

            $clients = $response->devices ?? [$this->getConnectionToken($connection)];
            $this->sendDataTo($response, $clients);
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
        }

        $request['api_token'] = $token;

        return $request;
    }

    private function sendDataTo($data, $clients)
    {
        foreach ($this->connections as $connection) {
            $token = $this->getConnectionToken($connection);
            if (in_array($token, $clients)) {
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
