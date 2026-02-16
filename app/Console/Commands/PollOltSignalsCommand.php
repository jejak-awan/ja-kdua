<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CollectOltSignalsJob;
use Illuminate\Console\Command;

class PollOltSignalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:poll-olt-signals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll all ONUs for optical signal levels and store in database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching CollectOltSignalsJob...');
        
        CollectOltSignalsJob::dispatchSync();
        
        $this->info('Signal polling completed.');
        
        return Command::SUCCESS;
    }
}
