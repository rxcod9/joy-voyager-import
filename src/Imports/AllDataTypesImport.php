<?php

namespace Joy\VoyagerImport\Imports;

// use App\Models\User;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use TCG\Voyager\Facades\Voyager;

class AllDataTypesImport implements
    WithMultipleSheets
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
            $sheets[] = new DataTypeImport($dataType);
        }

        return $sheets;
    }

    /**
     * @param  OutputStyle $output
     * @return $this
     */
    public function withOutput(OutputStyle $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return OutputStyle
     */
    public function getConsoleOutput(): OutputStyle
    {
        if (!$this->output instanceof OutputStyle) {
            $this->output = new OutputStyle(new StringInput(''), new NullOutput());
        }

        return $this->output;
    }
}
