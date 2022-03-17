<?php

namespace Joy\VoyagerImport\Http\Traits;

use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

trait ImportTemplateAction
{
    //***************************************
    //               ____
    //              |  _ \
    //              | |_) |
    //              |  _ <
    //              | |_) |
    //              |____/
    //
    //      ImportTemplate DataTable our Data Type (B)READ
    //
    //****************************************

    public function importTemplate(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $writerType = $request->get('writerType', $this->writerType ?? config('joy-voyager-import.writerType', Excel::XLSX));
        $fileName   = $this->fileName ?? ($dataType->slug . '.' . Str::lower($writerType));

        $exportClass = 'joy-voyager-import.import-template';

        if (app()->bound('joy-voyager-import.' . $dataType->slug . '.import-template')) {
            $exportClass = 'joy-voyager-import.' . $dataType->slug . '.import-template';
        }

        $export = app($exportClass);

        return $export->set(
            $dataType,
            [],
            $request->all(),
        )->download(
            $fileName,
            $writerType
        );
    }
}
