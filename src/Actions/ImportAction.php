<?php

namespace Joy\VoyagerImport\Actions;

use Joy\VoyagerImport\Imports\DataTypeImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use TCG\Voyager\Actions\AbstractAction;
use TCG\Voyager\Facades\Voyager;
use Maatwebsite\Excel\Excel;

class ImportAction extends AbstractAction
{
    /**
     * Optional File Name
     */
    protected $fileName;

    /**
     * Optional Reader Type
     */
    protected $readerType;

    public function getTitle()
    {
        return 'Import';
    }

    public function getIcon()
    {
        return 'voyager-upload';
    }

    public function getPolicy()
    {
        return 'write';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary',
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

        $readerType = $this->readerType ?? config('joy-voyager-import.readerType', Excel::XLSX);
        $fileName   = $this->fileName ?? ($dataType->slug . '.' . Str::lower($readerType));

        return (new DataTypeImport(
            $dataType,
            array_filter($ids),
            request()->all(),
        ))->import(
            $fileName,
            $readerType
        );
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
