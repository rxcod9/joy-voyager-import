<?php

namespace Joy\VoyagerImport\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Actions\AbstractAction;
use TCG\Voyager\Facades\Voyager;
use Maatwebsite\Excel\Excel;

class ImportAction extends AbstractAction
{
    /**
     * Optional mimes
     */
    protected $mimes;

    /**
     * Optional File Path
     */
    protected $filePath;

    /**
     * Optional Disk
     */
    protected $disk;

    /**
     * Optional Reader Type
     */
    protected $readerType;

    public function getTitle()
    {
        return __('joy-voyager-import::generic.bulk_import');
    }

    public function getIcon()
    {
        return 'voyager-upload';
    }

    public function getPolicy()
    {
        return 'browse';
    }

    public function getAttributes()
    {
        return [
            'id'    => 'bulk_import_btn',
            'class' => 'btn btn-primary',
        ];
    }

    public function getDefaultRoute()
    {
        // return route('my.route');
    }

    public function shouldActionDisplayOnDataType()
    {
        return config('joy-voyager-import.enabled', true) !== false
            && isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-import.allowed_slugs', ['*'])
            )
            && !isInPatterns(
                $this->dataType->slug,
                config('joy-voyager-import.not_allowed_slugs', [])
            );
    }

    public function massAction($ids, $comingFrom)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug(request());

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Gate::authorize('browse', app($dataType->model_name));

        $mimes = $this->mimes ?? config('joy-voyager-import.allowed_mimes');

        $validator = Validator::make(request()->all(), [
            'file' => 'required|mimes:' . $mimes,
        ]);

        if ($validator->fails()) {
            return redirect($comingFrom)->with([
                'message'    => $validator->errors()->first(),
                'alert-type' => 'error',
            ]);
        }

        $disk       = $this->disk ?? config('joy-voyager-import.disk');
        $readerType = $this->readerType ?? config('joy-voyager-import.readerType', Excel::XLSX);

        $importClass = 'joy-voyager-import.import';

        if (app()->bound("joy-voyager-import.$slug.import")) {
            $importClass = "joy-voyager-import.$slug.import";
        }

        $import = app($importClass);

        $import->set(
            $dataType,
            request()->all(),
        )->import(
            request()->file('file'),
            $disk,
            $readerType
        );

        return redirect($comingFrom)->with([
            'message'    => __('joy-voyager-import::generic.successfully_imported') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }

    public function massView()
    {
        $view = 'joy-voyager-import::bread.import';

        if (view()->exists('joy-voyager-import::' . $this->dataType->slug . '.import')) {
            $view = 'joy-voyager-import::' . $this->dataType->slug . '.import';
        }
        return $view;
    }

    protected function getSlug(Request $request)
    {
        if (isset($this->slug)) {
            $slug = $this->slug;
        } else {
            $slug = explode('.', $request->route()->getName())[1];
        }

        return $slug;
    }
}
