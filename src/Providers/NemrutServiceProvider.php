<?php

namespace Nemrut\Providers;

use Illuminate\Support\ServiceProvider;
use Nemrut\Console\Commands\SocketClient;
use Nemrut\Console\Commands\SocketServer;

class NemrutServiceProvider extends ServiceProvider
{

    protected $commands = ['SocketServer' => 'command.socket-server', 'SocketClient' => 'command.socket-client'];

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton('command.socket-server', function () {
            return new SocketServer();
        });

        $this->app->singleton('command.socket-client', function () {
            return new SocketClient();
        });

        $this->commands([
            'command.socket-server',
            'command.socket-client'
        ]);
    }
}
