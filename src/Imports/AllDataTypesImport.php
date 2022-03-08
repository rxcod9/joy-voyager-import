<?php

namespace Joy\VoyagerImport\Imports;

// use App\Models\User;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use TCG\Voyager\Facades\Voyager;

class AllDataTypesImport implements
    WithMultipleSheets,
    WithProgressBar
{
    use Importable;

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets    = [];
        $dataTypes = Voyager::model('DataType')->get();

        foreach ($dataTypes as $dataType) {
            $importClass = 'joy-voyager-import.import';

            if (app()->bound('joy-voyager-import.' . $dataType->slug . '.import')) {
                $importClass = 'joy-voyager-import.' . $dataType->slug . '.import';
            }

            $import = app($importClass);

            $sheets[$dataType->getTranslatedAttribute('display_name_plural')] = $import->set($dataType);
        }

        return $sheets;
    }
}
