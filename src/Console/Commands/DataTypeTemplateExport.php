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

class DataTypeTemplateExport extends Command
{
    protected $name = 'joy-import:export-template';

    protected $description = 'Joy Voyager DataType template exporter';

    public function handle()
    {
        $this->output->title('Starting export');

        $dataType = Voyager::model('DataType')->whereSlug($this->argument('slug'))->firstOrFail();

        $this->exportDataType(
            $dataType,
            $this->option('disk'),
            $this->option('writerType')
        );

        $this->output->success('Export successful');
    }

    protected function exportDataType(
        DataType $dataType,
        string $disk = null,
        string $writerType = Excel::XLSX
    ) {
        $path = 'public/imports/' . $dataType->slug . '-' . date('YmdHis') . '.' . Str::lower($writerType);

        $url = config('app.url') . Storage::disk($disk)->url($path);

        $this->output->info(sprintf(
            'Exporting to >>' . PHP_EOL . 'path : %s' . PHP_EOL . 'url : %s',
            storage_path($path),
            $url
        ));

        $exportClass = 'joy-voyager-import.import-template';

        if (app()->bound('joy-voyager-import.' . $dataType->slug . '.import-template')) {
            $exportClass = 'joy-voyager-import.' . $dataType->slug . '.import-template';
        }

        $export = app($exportClass);

        $export->set(
            $dataType,
            [],
            $this->options()
        )->withOutput(
            $this->output
        )->store(
            $path,
            $disk,
            $writerType
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
            ['slug', InputArgument::REQUIRED, 'The DataType slug which you want to export'],
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
                'The disk to where you want to export',
                config('joy-voyager-import.disk')
            ],
            [
                'writerType',
                'w',
                InputOption::VALUE_OPTIONAL,
                'The writerType in which format you want to export',
                config('joy-voyager-import.writerType', 'Xlsx')
            ],
        ];
    }
}
