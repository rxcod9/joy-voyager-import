<?php

namespace Joy\VoyagerImport\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;

trait ImportAction
{
    //***************************************
    //               ____
    //              |  _ \
    //              | |_) |
    //              |  _ <
    //              | |_) |
    //              |____/
    //
    //      Import DataTable our Data Type (B)READ
    //
    //****************************************

    public function import(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $mimes = $this->mimes ?? config('joy-voyager-import.allowed_mimes');

        $validator = Validator::make(request()->all(), [
            'file' => 'required|mimes:' . $mimes,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with([
                'message'    => $validator->errors()->first(),
                'alert-type' => 'error',
            ]);
        }

        $disk       = $request->get('disk', config('joy-voyager-import.disk'));
        $readerType = $request->get('readerType', config('joy-voyager-import.readerType', Excel::XLSX));

        $importClass = 'joy-voyager-import.import';

        if (app()->bound('joy-voyager-import.' . $dataType->slug . '.import')) {
            $importClass = 'joy-voyager-import.' . $dataType->slug . '.import';
        }

        $import = app($importClass);

        $import->set(
            $dataType,
            $request->all(),
        )->import(
            request()->file('file'),
            $disk,
            $readerType
        );

        return redirect()->back()->with([
            'message'    => __('joy-voyager-import::generic.successfully_imported') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }
}
