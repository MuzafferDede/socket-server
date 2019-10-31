<?php

namespace Nemrut\Console\Commands;

use Nemrut\Client;
use Illuminate\Console\Command;

class SocketClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket-client {action} {api_token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Conect Socket Client on the Socket server";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new Client($this->argument('action'), $this->argument('api_token')))->run();
    }
}
