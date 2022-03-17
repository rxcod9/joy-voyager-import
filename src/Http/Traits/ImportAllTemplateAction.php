<?php

namespace Joy\VoyagerImport\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

trait ImportAllTemplateAction
{
    //***************************************
    //               ____
    //              |  _ \
    //              | |_) |
    //              |  _ <
    //              | |_) |
    //              |____/
    //
    //      ImportAllTemplate DataTable our Data Type (B)READ
    //
    //****************************************

    public function importTemplateAll(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // Check permission
        $this->authorize('browse_bread');

        $writerType = $this->writerType ?? config('joy-voyager-import.writerType', Excel::XLSX);
        $fileName   = $this->fileName ?? ('import-all' . '.' . Str::lower($writerType));

        $exportClass = 'joy-voyager-import.import-all-template';

        $export = app($exportClass);

        return $export->set(
            $request->all(),
        )->download(
            $fileName,
            $writerType
        );
    }
}
