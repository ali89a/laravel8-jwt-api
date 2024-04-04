<?php

namespace App\Console\Commands;

use App\Models\Number;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NumberGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'number:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dummy Number Generate';

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
        $number = new Number();
        $number->number = rand(1000, 9999);
        $number->save();

        Log::info('Random Number Generated Successful..............');
    }
}
