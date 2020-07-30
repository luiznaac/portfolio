<?php

namespace App\Console\Commands;

use App\Portfolio\Consolidator\ConsolidatorExecutor;
use App\User;
use Illuminate\Console\Command;

class Consolidate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consolidate {user_id}';

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
     * @return int
     */
    public function handle()
    {
        /** @var User $user */
        $user = User::find($this->argument('user_id'));
        ConsolidatorExecutor::execute($user);

        return 0;
    }
}
