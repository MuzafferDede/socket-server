<?php

namespace Nemrut\Providers;

use Illuminate\Support\ServiceProvider;
use Nemrut\Console\Commands\SocketServer;

class NemrutServiceProvider extends ServiceProvider
{

    protected $commands = ['SocketServer' => 'command.socket-server'];

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton('command.socket-server', function () {
            return new SocketServer();
        });

        $this->commands([
            'command.socket-server'
        ]);
    }
}
