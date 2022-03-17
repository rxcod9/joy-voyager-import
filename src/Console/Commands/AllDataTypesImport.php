<?php

namespace Joy\VoyagerImport\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AllDataTypesImport extends Command
{
    protected $name = 'joy-import:all';

    protected $description = 'Joy Voyager all DataTypes importer';

    public function handle()
    {
        $this->output->title('Starting import');
        $this->importAllDataTypes(
            $this->argument('path'),
            $this->option('disk'),
            $this->option('readerType')
        );
        $this->output->success('Import successful');
    }

    protected function importAllDataTypes(
        string $path,
        string $disk = null,
        string $readerType = Excel::XLSX
    ) {
        $this->output->info(sprintf(
            'Importing from <<' . PHP_EOL . 'path : %s',
            $path,
        ));

        $importClass = 'joy-voyager-import.import';

        $import = app($importClass);

        $import->set(
            $this->options()
        )->withOutput(
            $this->output
        )->import(
            $path,
            $disk,
            $readerType
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['path', InputArgument::REQUIRED, 'The import file path'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'disk',
                'd',
                InputOption::VALUE_OPTIONAL,
                'The disk to where you want to import',
                config('joy-voyager-import.disk')
            ],
            [
                'readerType',
                'w',
                InputOption::VALUE_OPTIONAL,
                'The readerType in which format you want to import',
                config('joy-voyager-import.readerType', 'Xlsx')
            ],
        ];
    }
}
