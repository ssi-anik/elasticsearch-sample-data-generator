<?php

namespace App\Commands;

use Faker\Generator;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class GenerateCommand extends Command
{
    private $filePointer;

    protected $signature = 'generate 
                            {fields : Enter the fields definition (required)}
                            {--file=dump.json : Enter the file name}
                            {--entries=1 : Enter the number of entries}
                            {--action=index : Enter the action name [index or create]}
                            {--index=my-es-index : Enter the index name}
                            {--id=1 : Enter the sequence start value}
                            {--append : Append to existing file}
                            ';

    protected $description = 'Generate dump for elasticsearch bulk API upload';

    public function handle(Generator $faker)
    {
        $fields = $this->argument('fields');
        $file = $this->option('file');
        $entries = $this->getNoOfEntries();
        $action = $this->option('action');
        $index = $this->option('index');
        $docStartId = $this->documentStartId();
        $mode = $this->option('append') ? 'a' : 'w';

        /**
         * Ask user for confirmation
         */
        $this->info('The following values will be considered.');
        $this->table(['For the', 'will use'], [
            ['File', $file],
            ['No of entries', $entries],
            ['Action type', $action],
            ['Index', $index],
            ['Document ID starts from', $docStartId],
            ['Append if file exists', $mode === 'a' ? 'True' : 'False'],
        ]);

        /*if (false === $this->confirm('Proceed?', true)) {
            $this->warn('Exiting...');

            return;
        }*/
        /**
         * User confirmed
         */
        if (false === $this->canCreateFile($file, $mode)) {
            $this->output->error('Cannot create file on ' . $file);

            return;
        }

        $this->closeFile();
    }

    private function writeToFile(string $line)
    {
        fwrite($this->filePointer, $line);
    }

    private function closeFile()
    {
        fclose($this->filePointer);
    }

    private function canCreateFile($file, $mode = 'w'): bool
    {
        try {
            return !!($this->filePointer = fopen($file, $mode));
        } catch (Throwable $t) {
            return false;
        }
    }

    private function getNoOfEntries(): int
    {
        $noOfEntries = (int)$this->option('entries');

        return $noOfEntries > 0 ? $noOfEntries : 1;
    }

    private function documentStartId(): int
    {
        $docStartID = (int)$this->option('id');

        return $docStartID >= 0 ? $docStartID : 1;
    }
}
