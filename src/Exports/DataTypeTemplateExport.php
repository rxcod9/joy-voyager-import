<?php

namespace Joy\VoyagerImport\Exports;

// use App\Models\User;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Joy\VoyagerCore\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Models\DataType;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Joy\VoyagerImport\Events\BreadDataTemplateExported;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeWriting;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use TCG\Voyager\Facades\Voyager;

class DataTypeTemplateExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithTitle,
    WithEvents
{
    use BreadRelationshipParser;
    use Exportable;

    /**
     * The dataType.
     *
     * @var DataType
     */
    protected DataType $dataType;

    /**
     * The ids.
     *
     * @var array
     */
    protected $ids;

    /**
     * The input.
     *
     * @var array
     */
    protected $input;

    /**
     * The dataTypeContent.
     *
     * @var Model|null
     */
    protected $dataTypeContent;

    /**
     * @param DataType $dataType
     * @param array    $ids
     * @param array    $input
     */
    public function set(
        DataType $dataType,
        $ids = [],
        $input = []
    ) {
        $this->dataType        = $dataType;
        $this->ids             = $ids;
        $this->input           = $input;
        $this->dataTypeContent = app($this->dataType->model_name);
        return $this;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            BeforeWriting::class => function (BeforeWriting $event) {
                event(new BreadDataTemplateExported($this->dataType, $this->input));
            },
        ];
    }

    public function collection()
    {
        $orderBy = Arr::get(
            $this->input,
            'columns.' . Arr::get($this->input, 'order.0.column', 0) . '.name',
            $this->dataType->order_column
        );
        $sortOrder       = Arr::get($this->input, 'order.0.dir', $this->dataType->order_direction);
        $usesSoftDeletes = false;
        $showSoftDeleted = false;

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($this->dataType->model_name) != 0) {
            $model = app($this->dataType->model_name);

            $query = $model::select($this->dataType->name . '.*');

            if ($this->dataType->scope && $this->dataType->scope != '' && method_exists($model, 'scope' . ucfirst($this->dataType->scope))) {
                $query->{$this->dataType->scope}();
            }

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model)) && Auth::user()->can('delete', app($this->dataType->model_name))) {
                $usesSoftDeletes = true;

                if (Arr::get($this->input, 'showSoftDeleted')) {
                    $showSoftDeleted = true;
                    $query->withTrashed();
                }
            }

            // If a column has a relationship associated with it, we do not want to show that field
            // $this->removeRelationshipField($this->dataType, 'browse');

            $row = $this->dataType->rows->where('field', $orderBy)->firstWhere('type', 'relationship');
            if ($orderBy && (in_array($orderBy, $this->dataType->fields()) || !empty($row))) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'desc';
                if (!empty($row)) {
                    $query->select([
                        $this->dataType->name . '.*',
                        'joined.' . $row->details->label . ' as ' . $orderBy,
                    ])->leftJoin(
                        $row->details->table . ' as joined',
                        $this->dataType->name . '.' . $row->details->column,
                        'joined.' . $row->details->key
                    );
                }

                $query->orderBy($orderBy, $querySortOrder);
            } elseif ($model->timestamps) {
                $query->latest($model::CREATED_AT);
            } else {
                $query->orderBy($model->getKeyName(), 'DESC');
            }
        } else {
            // If Model doesn't exist, get data from table name
            $query = DB::table($this->dataType->name);

            $model = false;
        }

        // Export only selected
        if ($this->ids) {
            $query->whereKey($this->ids);
        }

        return $query->limit(2)->get();
    }

    public function headings(): array
    {
        // If a column has a relationship associated with it, we do not want to show that field
        // $this->removeRelationshipField($this->dataType, 'browse');

        $headings = [];
        // $headings[] = 'id'; // index column
        foreach ($this->dataType->rows as $row) {
            $headings[$row->field] = $row->field;
        }
        return $headings;
    }

    /**
     * @var Model $data
     */
    public function map($data): array
    {
        // If a column has a relationship associated with it, we do not want to show that field
        // $this->removeRelationshipField($this->dataType, 'browse');

        $columns = [];
        // $columns[] = $data->id;

        foreach ($this->dataType->rows as $row) {
            $column = null;
            if ($data->{$row->field . '_export'}) {
                $data->{$row->field} = $data->{$row->field . '_export'};
            } elseif ($data->{$row->field . '_browse'}) {
                $data->{$row->field} = $data->{$row->field . '_browse'};
            }

            if (isset($row->details->view)) {
                $column = trim(strip_tags((string) view($row->details->view, [
                    'row'             => $row,
                    'dataType'        => $this->dataType,
                    'data'            => $data,
                    'dataTypeContent' => $this->dataTypeContent,
                    'content'         => $data->{$row->field},
                    'action'          => 'browse',
                    'view'            => 'browse',
                    'options'         => $row->details
                ])));
            } elseif ($row->type == 'image') {
                // if (!filter_var($data->{$row->field}, FILTER_VALIDATE_URL)) {
                //     $column = Voyager::image($data->{$row->field});
                // } else {
                $column = $data->{$row->field};
            // }
            } elseif ($row->type == 'relationship') {
                $column = trim(strip_tags((string) view('voyager::formfields.relationship', [
                    'view'            => 'browse',
                    'row'             => $row,
                    'dataType'        => $this->dataType,
                    'data'            => $data,
                    'dataTypeContent' => $this->dataTypeContent,
                    'content'         => $data->{$row->field},
                    'action'          => 'browse',
                    'view'            => 'browse',
                    'options'         => $row->details
                ])));
            } elseif ($row->type == 'select_multiple') {
                $values = [];
                if (property_exists($row->details, 'relationship')) {
                    foreach ($data->{$row->field} as $item) {
                        $values[] = $item->{$row->field};
                    }
                } elseif (property_exists($row->details, 'options')) {
                    if (!empty(json_decode($data->{$row->field}))) {
                        foreach (json_decode($data->{$row->field}) as $item) {
                            if (@$row->details->options->{$item}) {
                                $values[] = $row->details->options->{$item};
                            }
                        }
                    } else {
                        $values[] = __('voyager::generic.none');
                    }
                }
                $column = implode(', ', $values);
            } elseif ($row->type == 'multiple_checkbox' && property_exists($row->details, 'options')) {
                $values = [];
                if (@count(json_decode($data->{$row->field})) > 0) {
                    foreach (json_decode($data->{$row->field}) as $item) {
                        if (@$row->details->options->{$item}) {
                            $values[] = $row->details->options->{$item};
                        }
                    }
                } else {
                    $values[] = __('voyager::generic.none');
                }
                $column = implode(', ', $values);
            } elseif (($row->type == 'select_dropdown' || $row->type == 'radio_btn') && property_exists($row->details, 'options')) {
                $column = $row->details->options->{$data->{$row->field}} ?? '';
            } elseif ($row->type == 'date' || $row->type == 'timestamp') {
                if (property_exists($row->details, 'format') && !is_null($data->{$row->field})) {
                    $column = \Carbon\Carbon::parse($data->{$row->field})->isoFormat($row->details->format);
                } else {
                    $column = $data->{$row->field};
                }
            } elseif ($row->type == 'checkbox') {
                if (property_exists($row->details, 'on') && property_exists($row->details, 'off')) {
                    if ($data->{$row->field}) {
                        $column = $row->details->on;
                    } else {
                        $column = $row->details->off;
                    }
                } else {
                    $column = $data->{$row->field};
                }
            } elseif ($row->type == 'color') {
                $column = $data->{$row->field};
            } elseif ($row->type == 'text') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $column = mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field};
                $column = $data->{$row->field};
            } elseif ($row->type == 'text_area') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $column = mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field};
                $column = $data->{$row->field};
            } elseif ($row->type == 'file' && !empty($data->{$row->field})) {
                $values = [];
                // view('voyager::multilingual.input-hidden-bread-browse');
                if (json_decode($data->{$row->field}) !== null) {
                    foreach (json_decode($data->{$row->field}) as $file) {
                        $values[] = Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '';
                    }
                } else {
                    $values[] = Storage::disk(config('voyager.storage.disk'))->url($data->{$row->field});
                }
                $column = implode(', ', $values);
            } elseif ($row->type == 'rich_text_box') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $column = mb_strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? mb_substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>');
                $column = strip_tags($data->{$row->field}, '<b><i><u>');
            } elseif ($row->type == 'coordinates') {
                $url = 'https://maps.googleapis.com/maps/api/staticmap?zoom=' . config('voyager.googlemaps.zoom') . '&size=400x100&maptype=roadmap&';
                foreach ($data->getCoordinates() as $point) {
                    $url .= 'markers=color:red%7C' . $point['lat'] . ',' . $point['lng'] . '&center=' . $point['lat'] . ',' . $point['lng'];
                }
                $url .= '&key=' . config('voyager.googlemaps.key');
                // $column = view('voyager::partials.coordinates-static-image');
                $column = $url;
            } elseif ($row->type == 'multiple_images') {
                $values = [];
                $images = json_decode($data->{$row->field});
                if ($images) {
                    $images = array_slice($images, 0, 3);
                    foreach ($images as $image) {
                        if (!filter_var($image, FILTER_VALIDATE_URL)) {
                            $values[] = Voyager::image($image);
                        } else {
                            $values[] = $image;
                        }
                    }
                }
                $column = implode(', ', $values);
            } elseif ($row->type == 'media_picker') {
                $values = [];

                if (is_array($data->{$row->field})) {
                    $files = $data->{$row->field};
                } else {
                    $files = json_decode($data->{$row->field});
                }

                if ($files) {
                    if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images) {
                        foreach (array_slice($files, 0, 3) as $file) {
                            if (!filter_var($file, FILTER_VALIDATE_URL)) {
                                $values[] = Voyager::image($file);
                            } else {
                                $values[] = $file;
                            }
                        }
                    } else {
                        foreach (array_slice($files, 0, 3) as $file) {
                            $values[] = $file;
                        }
                    }
                    if (count($files) > 3) {
                        $values[] = __('voyager::media.files_more', ['count' => (count($files) - 3)]);
                    }
                } elseif (is_array($files) && count($files) == 0) {
                    $values[] = trans_choice('voyager::media.files', 0);
                } elseif ($data->{$row->field} != '') {
                    if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images) {
                        if (!filter_var($data->{$row->field}, FILTER_VALIDATE_URL)) {
                            $values[] = Voyager::image($data->{$row->field});
                        } else {
                            $values[] = $data->{$row->field};
                        }
                    } else {
                        $values[] = $data->{$row->field};
                    }
                } else {
                    $values[] = trans_choice('voyager::media.files', 0);
                }
                $column = implode(', ', $values);
            } else {
                // view('voyager::multilingual.input-hidden-bread-browse');
                $column = $data->{$row->field};
            }
            $columns[] = $column;
        }
        return $columns;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->dataType->getTranslatedAttribute('display_name_plural');
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
