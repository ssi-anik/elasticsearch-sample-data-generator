<?php

namespace App\Commands;

use Faker\Generator;
use LaravelZero\Framework\Commands\Command;

class GenerateCommand extends Command
{
    protected $signature = 'generate 
                            {fields : Enter the fields definition (required)}
                            {--file=dump.json : Enter the file name}
                            {--entries=1 : Enter the number of entries}
                            {--action=index : Enter the action name [index or create]}
                            {--index=my-es-index : Enter the index name}
                            {--id=1 : Enter the sequence start value}
                            ';

    protected $description = 'Generate dump for elasticsearch bulk API upload';

    public function handle(Generator $faker)
    {
        $fields = $this->argument('fields');
        $file = $this->option('file');
        $entries = $this->option('entries');
        $action = $this->option('action');
        $index = $this->option('index');
        $docStartId = $this->option('id');

        $this->info('The following values will be considered.');
        $this->table(['For the', 'will use'], [
            ['File', $file],
            ['No of entries', $entries],
            ['Action type', $action],
            ['Index', $index],
            ['Document ID starts from', $docStartId],
        ]);
        /*if (false === $this->confirm('Proceed?', true)) {
            $this->warn('Exiting...');

            return;
        }*/
        dd($faker->custom('designation'));
    }
}
