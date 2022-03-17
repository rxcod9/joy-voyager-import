<?php

namespace Joy\VoyagerBulkUpdate\Imports;

use Joy\VoyagerBulkUpdate\Events\AllBreadDataImported;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Events\AfterImport;
use TCG\Voyager\Facades\Voyager;

class AllDataTypesImport implements
    WithMultipleSheets,
    WithProgressBar,
    WithEvents
{
    use Importable;

    /**
     * The input.
     *
     * @var array
     */
    protected $input = [];

    /**
     * @param array $input
     */
    public function set(
        $input = []
    ) {
        $this->input = $input;
        return $this;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            AfterImport::class => function (AfterImport $event) {
                event(new AllBreadDataImported($this->input));
            },
        ];
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets    = [];
        $dataTypes = Voyager::model('DataType')->get();

        foreach ($dataTypes as $dataType) {
            $importClass = 'joy-voyager-bulk-update.import';

            if (app()->bound('joy-voyager-bulk-update.' . $dataType->slug . '.import')) {
                $importClass = 'joy-voyager-bulk-update.' . $dataType->slug . '.import';
            }

            $import = app($importClass);

            $sheets[$dataType->getTranslatedAttribute('display_name_plural')] = $import->set(
                $dataType,
                [],
                $this->input
            );
        }

        return $sheets;
    }
}
