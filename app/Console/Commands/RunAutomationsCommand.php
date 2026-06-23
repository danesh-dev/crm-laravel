<?php

namespace App\Console\Commands;

use App\Services\Automation\AutomationEngine;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Run Automations')]
class RunAutomationsCommand extends Command
{
    protected $signature = 'automation:run';
    /**
     * Execute the console command.
     */
    public function handle(
        AutomationEngine $engine
    ): int {

        $engine->run();

        return self::SUCCESS;
    }
}
