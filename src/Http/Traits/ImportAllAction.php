<?php

namespace Joy\VoyagerImport\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Joy\VoyagerImport\Jobs\AsyncImport;
use Maatwebsite\Excel\Excel;

trait ImportAllAction
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

    public function importAll(Request $request)
    {
        // Check permission
        $this->authorize('browse_bread');

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

        $importClass = 'joy-voyager-import.import-all';

        $import = app($importClass);

        $import->set(
            request()->all(),
        );

        $file = request()->file('file');

        $path = 'imports/' . $file->hashName();

        Storage::disk($disk)->put('imports', $file);

        if (!$this->shouldImportAllAsync($import)) {
            $import->import(
                $path,
                $disk,
                $readerType
            );

            return redirect()->back()->with([
                'message'    => __('joy-voyager-import::generic.successfully_imported_all'),
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
            'message'    => __('joy-voyager-import::generic.successfully_imported_all_queued'),
            'alert-type' => 'success',
        ]);
    }

    protected function shouldImportAllAsync($import)
    {
        return config('joy-voyager-import.all_async', false) === true;
    }
}
