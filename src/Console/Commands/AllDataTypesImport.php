<?php

namespace Joy\VoyagerImport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Joy\VoyagerImport\Imports\AllDataTypesImport as ExportsAllDataTypesExport;
use Maatwebsite\Excel\Excel;
use Symfony\Component\Console\Input\InputOption;

class AllDataTypesImport extends Command
{
    protected $name = 'joy-import:all';

    protected $description = 'Joy Voyager all DataTypes importer';

    public function handle()
    {
        $this->output->title('Starting import');
        $this->importAllDataTypes(
            $this->option('disk'),
            $this->option('readerType')
        );
        $this->output->success('Import successful');
    }

    protected function importAllDataTypes(
        string $disk = null,
        string $readerType = Excel::XLSX
    ) {
        $path = 'public/imports/' . (($filePath ?? 'import-all') . '-' . date('YmdHis') . '.' . Str::lower($readerType));

        $url = config('app.url') . Storage::disk($disk)->url($path);

        $this->output->info(sprintf(
            'Importing to >>' . PHP_EOL . 'path : %s' . PHP_EOL . 'url : %s',
            storage_path($path),
            $url
        ));

        (new ImportsAllDataTypesImport())->withOutput($this->output)->import(
            $path,
            $disk,
            $readerType
        );
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
