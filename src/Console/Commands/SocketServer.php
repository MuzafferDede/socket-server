<?php

namespace Nemrut\Console\Commands;

use Nemrut\Server;
use Illuminate\Console\Command;

class SocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket-server {--port=9000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Serve the application on the Socket server";

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
        (new Server($this->option('port')))->run();
    }
}
