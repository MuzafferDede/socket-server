<?php

namespace Nemrut\Console\Commands;

use Nemrut\Server;
use Illuminate\Console\Command;

class SocketServe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket-serve  {--host=$_SERVER["SERVER_ADDR"]} {--port=9000} {--path=local}';

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
        $host = $this->option('host');

        $port = $this->option('port');

        $path = $this->option('path');

        $this->info("Laravel Socket server started on http://{$host}:{$port}. Send POST request to /{$path}");

        (new Server($host, $port, $path))->run();
    }
}
