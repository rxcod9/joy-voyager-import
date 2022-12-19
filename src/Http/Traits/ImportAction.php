<?php

namespace Joy\VoyagerImport\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Joy\VoyagerImport\Jobs\AsyncImport;
use TCG\Voyager\Facades\Voyager;
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
            request()->all(),
        );

        $file = request()->file('file');

        $path = 'imports/' . $file->hashName();

        Storage::disk($disk)->put('imports', $file);

        if (!$this->shouldImportAsync($import)) {
            $import->import(
                $path,
                $disk,
                $readerType
            );

            return redirect()->back()->with([
                'message'    => __('joy-voyager-import::generic.successfully_imported') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        }

        AsyncImport::dispatch(
            request()->user(),
            $import,
            $path,
            $disk,
            $readerType
        );

        return redirect()->back()->with([
            'message'    => __('joy-voyager-import::generic.successfully_import_queued') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }

    protected function shouldImportAsync($import)
    {
        return config('joy-voyager-import.async', false) === true;
    }
}
