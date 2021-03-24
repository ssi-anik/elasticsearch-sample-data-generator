<?php

namespace App\Commands;

use Faker\Generator;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Ramsey\Uuid\Uuid;
use Throwable;

class GenerateCommand extends Command
{
    private $filePointer;
    private $fields;
    private $file;
    private $entries;
    private $action;
    private $index;
    private $docStartId;
    private $mode;
    private $generator;

    protected $signature = 'generate 
                            {fields : Enter the fields definition (required)}
                            {--file=dumps/dump.json : Enter the file name}
                            {--entries=1 : Enter the number of entries}
                            {--action=index : Enter the action name [index or create]}
                            {--index=my-index : Enter the index name}
                            {--id=1 : Enter the sequence start value}
                            {--append : Append to existing file}
                            {--uuid : UUID based ID generation}
                            {--force : Does not ask for confirmation}
                            ';

    protected $description = 'Generate dump for elasticsearch bulk API upload';

    public function __construct(Generator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function __destruct()
    {
        $this->closeFile();
    }

    public function handle()
    {
        $this->output->title('Generate dump for Elasticsearch bulk API with fzaninotto/faker');

        $this->prepareOptions();
        if (false === $this->askForConfirmation()) {
            $this->warn('Exiting...');

            return;
        }

        if (false === $this->canCreateFile()) {
            return;
        }

        $this->generateAndWriteDump();
        /**
         * Space is to be printed to keep padding between the next output and progressbar
         */
        $this->output->writeln(' ');
        $this->output->success('Dump has been written to the file (' . $this->file . ') successfully.');
    }

    private function prepareOptions(): void
    {
        $this->fields = $this->argument('fields');
        $this->file = $this->getFilePath();
        $this->entries = $this->getNoOfEntries();
        $this->action = $this->getAction();
        $this->index = $this->option('index');
        $this->docStartId = $this->documentStartId();
        $this->mode = $this->option('append') ? 'a' : 'w';
    }

    private function askForConfirmation(): bool
    {
        $this->output->warning('The following values will be considered.');
        $this->table(['Options', 'Will use'], [
            ['File', $this->file],
            ['No of entries', $this->entries],
            ['Action type', $this->action],
            ['Index', $this->index],
            ['Document ID starts from', $this->docStartId],
            ['Append if file exists', $this->mode === 'a' ? 'True' : 'False'],
        ]);

        return $this->option('force') ? true : $this->confirm('Proceed?', true);
    }

    private function canCreateFile(): bool
    {
        try {
            return !!($this->filePointer = fopen($this->file, $this->mode));
        } catch (Throwable $t) {
            $this->output->error('Cannot create file on ' . $this->file);

            return false;
        }
    }

    private function writeToFile(string $line)
    {
        fwrite($this->filePointer, $line);
    }

    private function closeFile()
    {
        $this->filePointer ? fclose($this->filePointer) : null;
    }

    private function generateAndWriteDump()
    {
        $fields = $this->parseFields();
        $this->output->writeln(' ');
        $this->withProgressBar(range(1, $this->entries), function ($index) use ($fields) {
            $source = [];
            foreach ($fields as $field) {
                [$path, $method, $parameters] = $this->pathAndFieldResolver($field);
                $value = call_user_func_array([$this->generator, $method], $parameters);
                $source = array_merge_recursive($source, $this->dotNotationToArray($path, $value));
            }

            $actionMetadata = [
                $this->action => [
                    '_index' => $this->index,
                    '_id' => $this->option('uuid') ? Uuid::getFactory()->uuid4() : $this->docStartId + ($index - 1),
                ],
            ];

            $this->writeToFile(json_encode($actionMetadata) . PHP_EOL);
            $this->writeToFile(json_encode($source) . PHP_EOL);
        });
    }

    private function pathAndFieldResolver($field): array
    {
        $parts = array_filter(array_map('trim', explode(":", $field)));
        $path = $parts[0];
        // Use path as the resolver as it is shorthanded
        $resolver = $parts[1] ?? $path;

        // the resolver is like a property call
        if (false === strpos($resolver, '(')) {
            return [$path, $resolver, []];
        }

        // the resolver is the method
        $expression = "/\s*(\w+)\s*\((.*)\)/";
        preg_match($expression, $resolver, $extraction);
        $method = $extraction[1];
        $arguments = $extraction[2];

        /**
         * I Know what I am doing
         * Converting the method parameters in an array to pass
         * it on on the next faker call
         */
        eval("\$arguments = [{$arguments}];");

        return [$path, $method, $arguments];
    }

    private function dotNotationToArray($path, $value)
    {
        return array_reduce(array_reverse(array_filter(array_map('trim', explode('.', $path)))),
            function ($previous, $current) {
                return [$current => $previous];
            }, $value);
    }

    private function getAction()
    {
        return in_array($action = $this->option('action'), ['index', 'create']) ? $action : 'index';
    }

    private function getNoOfEntries(): int
    {
        $noOfEntries = (int)$this->option('entries');

        return $noOfEntries > 0 ? $noOfEntries : 1;
    }

    private function getFilePath(): string
    {
        $file = $this->option('file');
        $path = dirname($file);

        /**
         * I don't use windows
         * Cannot help with the file path
         * I'll always dump in `dumps` directory
         */
        if (Str::startsWith($path, '/')) {
            return $file;
        }

        return sprintf('dumps/%s', pathinfo($file)['basename']);
    }

    private function documentStartId(): int
    {
        $docStartID = (int)$this->option('id');

        return $docStartID >= 0 ? $docStartID : 1;
    }

    private function parseFields(): array
    {
        return array_filter(array_map('trim', explode('|', $this->fields)));
    }
}
