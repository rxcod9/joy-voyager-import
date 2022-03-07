<?php

namespace Joy\VoyagerImport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            $this->option('disk'),
            $this->option('readerType')
        );

        $this->output->success('Import successful');
    }

    protected function importDataType(
        DataType $dataType,
        string $disk = null,
        string $readerType = Excel::XLSX
    ) {
        $path = 'public/imports/' . $dataType->slug . '-' . date('YmdHis') . '.' . Str::lower($readerType);

        $url = config('app.url') . Storage::disk($disk)->url($path);

        $this->output->info(sprintf(
            'Importing to >>' . PHP_EOL . 'path : %s' . PHP_EOL . 'url : %s',
            storage_path($path),
            $url
        ));

        (new ImportsDataTypeImport($dataType))->withOutput($this->output)->import(
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
