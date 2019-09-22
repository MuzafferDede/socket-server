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
    protected $signature = 'socket-serve  {--host=127.0.0.1} {--port=9000}';

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

        $this->info("Laravel ReactPHP server started on http://{$host}:{$port}");

        (new Server($host, $port))->run();
    }
}
