<?php

namespace Techpanda\Core\Console;

use Backend\Models\User;
use Illuminate\Console\Command;
use Queue;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Techpanda\Core\Classes\SmsSender;
use Techpanda\Core\Models\Association;
use Techpanda\Core\Models\Transaction;

class DeleteOrphanTransaction extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'core:deleteorphanttnx';

    /**
     * @var string The console command description.
     */
    protected $description = 'Delete transactions those are in preview state, not finally submitted';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {

        $transactions = Transaction::withoutGlobalScopes()->where('is_preview_submitted', 0)->get();
        foreach ($transactions as $transaction) {
            $transaction->delete();
        }

        $this->output->writeln('done!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
