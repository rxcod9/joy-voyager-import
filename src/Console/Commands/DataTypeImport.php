<?php

namespace Joy\VoyagerBulkUpdate\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataType;

class DataTypeImport extends Command
{
    protected $name = 'joy-import';

    protected $description = 'Joy Voyager DataType importer';

    public function handle()
    {
        $this->output->title('Starting import');

        $dataType = Voyager::model('DataType')->whereSlug($this->argument('slug'))->firstOrFail();

        $this->importDataType(
            $dataType,
            $this->argument('path'),
            $this->option('disk'),
            $this->option('readerType')
        );

        $this->output->success('Import successful');
    }

    protected function importDataType(
        DataType $dataType,
        string $path,
        string $disk = null,
        string $readerType = Excel::XLSX
    ) {
        $this->output->info(sprintf(
            'Importing from <<' . PHP_EOL . 'path : %s',
            $path,
        ));

        $importClass = 'joy-voyager-bulk-update.import';

        if (app()->bound('joy-voyager-bulk-update.' . $dataType->slug . '.import')) {
            $importClass = 'joy-voyager-bulk-update.' . $dataType->slug . '.import';
        }

        $import = app($importClass);

        $import->set(
            $dataType,
            [],
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
            ['slug', InputArgument::REQUIRED, 'The DataType slug which you want to import'],
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
                config('joy-voyager-bulk-update.disk')
            ],
            [
                'readerType',
                'w',
                InputOption::VALUE_OPTIONAL,
                'The readerType in which format you want to import',
                config('joy-voyager-bulk-update.readerType', 'Xlsx')
            ],
        ];
    }
}
