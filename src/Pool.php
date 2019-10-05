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

        $this->setConnectionToken($connection, '');

        $connection->on('data', function ($data) use ($connection) {
            $this->handleRequest($data, $connection);
        });

        $connection->on('close', function () use ($connection) {
            $this->connections->offsetUnset($connection);
            echo $connection->getRemoteAddress() . " disconnected" . PHP_EOL;
        });
    }

    private function initConnection($data, ConnectionInterface $connection)
    {
        $params = json_decode($data, true);

        $token = $this->getConnectionToken($connection) || $params['api_token'] ?? $params['api_token'];

        if ($params['action'] == "000") {
            $connection->close();
            return false;
        }

        if (empty($token) || !$params) {
            $this->response('Something went wrong when reading JSON data', $connection, false);
            return false;
        }

        $this->setConnectionToken($connection, $token);

        return $params;
    }

    private function handleRequest($data, ConnectionInterface $connection)
    {
        if ($params = $this->initConnection($data, $connection)) {
            $request = Request::create($this->path, 'POST', $params);

            $request->headers->set('Accept',  "application/json");

            $response =  app()->handle($request);

            $clients = $params['devices'] ?? [$this->getConnectionToken($connection)];

            $this->sendDataTo($response->getContent(), $clients);
        }
    }

    private function sendDataTo($data, $clients)
    {
        foreach ($this->connections as $connection) {
            $token = $this->getConnectionToken($connection);
            if (in_array($token, $clients)) {
                $connection->write($data . PHP_EOL);
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
