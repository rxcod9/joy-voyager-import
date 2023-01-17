<?php

namespace Joy\VoyagerImport\Imports;

// use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Joy\VoyagerImport\Events\BreadDataImported;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Joy\VoyagerCore\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Models\DataType;

class DataTypeImport implements
    ToModel,
    WithHeadingRow,
    WithProgressBar,
    WithValidation,
    WithUpserts,
    WithEvents
{
    use BreadRelationshipParser;
    use Importable;

    /**
     * The dataType.
     *
     * @var DataType
     */
    protected DataType $dataType;

    /**
     * The input.
     *
     * @var array
     */
    protected $input;

    /**
     * @param DataType $dataType
     * @param array    $input
     */
    public function set(
        DataType $dataType,
        $input = []
    ) {
        $this->dataType = $dataType;
        $this->input    = $input;
        return $this;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            AfterImport::class => function (AfterImport $event) {
                event(new BreadDataImported($this->dataType, $this->input));
            },
        ];
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        $model = app($this->dataType->model_name);

        $dataTypeUniqueColumn = config(
            'joy-voyager-import.unique_column.' . $this->dataType->slug
        );

        if ($dataTypeUniqueColumn) {
            return $dataTypeUniqueColumn;
        }

        return $model->getKeyName();
    }

    public function rules(): array
    {
        $isValidationEnabled = config('joy-voyager-import.validation', false) === true;
        if (!$isValidationEnabled) {
            // return [];
        }

        $rules            = [];
        $messages         = [];
        $customAttributes = [];
        $id               = null;
        $is_update        = false; //$name && $id;

        $fieldsWithValidationRules = $this->getFieldsWithValidationRules($this->dataType->rows);

        foreach ($fieldsWithValidationRules as $field) {
            $fieldRules = $field->details->validation->rule;
            $fieldName  = $field->field;

            // Show the field's display name on the error message
            if (!empty($field->display_name)) {
                if (!empty($data[$fieldName]) && is_array($data[$fieldName])) {
                    foreach ($data[$fieldName] as $index => $element) {
                        if ($element instanceof UploadedFile) {
                            $name = $element->getClientOriginalName();
                        } else {
                            $name = $index + 1;
                        }

                        $customAttributes[$fieldName . '.' . $index] = $field->getTranslatedAttribute('display_name') . ' ' . $name;
                    }
                } else {
                    $customAttributes[$fieldName] = $field->getTranslatedAttribute('display_name');
                }
            }

            // If field is an array apply rules to all array elements
            $fieldName = !empty($data[$fieldName]) && is_array($data[$fieldName]) ? $fieldName . '.*' : $fieldName;

            // Get the rules for the current field whatever the format it is in
            $rules[$fieldName] = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            if ($id && property_exists($field->details->validation, 'edit')) {
                // $action_rules = $field->details->validation->edit->rule;
                // $rules[$fieldName] = array_merge($rules[$fieldName], (is_array($action_rules) ? $action_rules : explode('|', $action_rules)));
            } elseif (!$id && property_exists($field->details->validation, 'add')) {
                $action_rules      = $field->details->validation->add->rule;
                $rules[$fieldName] = array_merge($rules[$fieldName], (is_array($action_rules) ? $action_rules : explode('|', $action_rules)));
            }
            // Fix Unique validation rule on Edit Mode
            // if ($is_update) {
            foreach ($rules[$fieldName] as &$fieldRule) {
                if (strpos(strtoupper($fieldRule), 'UNIQUE') !== false) {
                    $fieldRule = null; // \Illuminate\Validation\Rule::unique($name)->ignore($id);
                }
            }
            // }

            // Set custom validation messages if any
            if (!empty($field->details->validation->messages)) {
                foreach ($field->details->validation->messages as $key => $msg) {
                    $messages["{$field->field}.{$key}"] = $msg;
                }
            }
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        $isValidationEnabled = config('joy-voyager-import.validation', false) === true;
        if (!$isValidationEnabled) {
            // return [];
        }

        $rules            = [];
        $messages         = [];
        $customAttributes = [];
        $id               = null;
        $is_update        = false; //$name && $id;

        $fieldsWithValidationRules = $this->getFieldsWithValidationRules($this->dataType->rows);

        foreach ($fieldsWithValidationRules as $field) {
            $fieldRules = $field->details->validation->rule;
            $fieldName  = $field->field;

            // Show the field's display name on the error message
            if (!empty($field->display_name)) {
                if (!empty($data[$fieldName]) && is_array($data[$fieldName])) {
                    foreach ($data[$fieldName] as $index => $element) {
                        if ($element instanceof UploadedFile) {
                            $name = $element->getClientOriginalName();
                        } else {
                            $name = $index + 1;
                        }

                        $customAttributes[$fieldName . '.' . $index] = $field->getTranslatedAttribute('display_name') . ' ' . $name;
                    }
                } else {
                    $customAttributes[$fieldName] = $field->getTranslatedAttribute('display_name');
                }
            }

            // If field is an array apply rules to all array elements
            $fieldName = !empty($data[$fieldName]) && is_array($data[$fieldName]) ? $fieldName . '.*' : $fieldName;

            // Get the rules for the current field whatever the format it is in
            $rules[$fieldName] = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            if ($id && property_exists($field->details->validation, 'edit')) {
                // $action_rules = $field->details->validation->edit->rule;
                // $rules[$fieldName] = array_merge($rules[$fieldName], (is_array($action_rules) ? $action_rules : explode('|', $action_rules)));
            } elseif (!$id && property_exists($field->details->validation, 'add')) {
                $action_rules      = $field->details->validation->add->rule;
                $rules[$fieldName] = array_merge($rules[$fieldName], (is_array($action_rules) ? $action_rules : explode('|', $action_rules)));
            }
            // Fix Unique validation rule on Edit Mode
            // if ($is_update) {
            foreach ($rules[$fieldName] as &$fieldRule) {
                if (strpos(strtoupper($fieldRule), 'UNIQUE') !== false) {
                    $fieldRule = null; // \Illuminate\Validation\Rule::unique($name)->ignore($id);
                }
            }
            // }

            // Set custom validation messages if any
            if (!empty($field->details->validation->messages)) {
                foreach ($field->details->validation->messages as $key => $msg) {
                    $messages["{$field->field}.{$key}"] = $msg;
                }
            }
        }

        return $messages;
    }

    /**
     * Get fields having validation rules in proper format.
     *
     * @param array $fieldsConfig
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFieldsWithValidationRules($fieldsConfig)
    {
        return $fieldsConfig->filter(function ($value) {
            if (empty($value->details)) {
                return false;
            }

            return !empty($value->details->validation->rule);
        });
    }

    /**
     * @param array $item
     *
     * @return Model
     */
    public function model(array $item)
    {
        $item = array_filter($item);
        if (!$item || empty($item)) {
            return null;
        }

        $model = app($this->dataType->model_name);
        $data  = new $model();

        // If a column has a relationship associated with it, we do not want to show that field
        // $this->removeRelationshipField($this->dataType, 'browse');

        $id = $item['id'] ?? $item[''] ?? null;
        if ($id) {
            $data->id = $item['id'] ?? $item[''] ?? null;
        }

        foreach ($this->dataType->rows as $row) {
            if ($data->hasSetMutator($row->field . '_import')) {
                $data->{$row->field . '_import'} = ($item[$row->field] ?? null);
            }

            if (isset($row->details->view)) {
                $data->{$row->field} = null;
            } elseif ($row->type == 'image') {
                if (!filter_var(($item[$row->field] ?? null), FILTER_VALIDATE_URL)) {
                    $data->{$row->field} = ($item[$row->field] ?? null);
                } else {
                    // @TODO remove starting string added by Voyager::image(
                    $data->{$row->field} = ($item[$row->field] ?? null);
                }
            } elseif ($row->type == 'relationship') {
                // @TODO handle each type of relationship
                // $data->{$row->field} = null; // ;
            } elseif ($row->type == 'select_multiple') {
                // $value = [];
                // if (property_exists($row->details, 'relationship')) {
                //     foreach (($item[$row->field] ?? null) as $item) {
                //         $value = $item->{$row->field};
                //     }
                // } elseif (property_exists($row->details, 'options')) {
                //     if (!empty(json_decode(($item[$row->field] ?? null)))) {
                //         foreach (json_decode(($item[$row->field] ?? null)) as $item) {
                //             if (@$row->details->options->{$item}) {
                //                 $value = $row->details->options->{$item};
                //             }
                //         }
                //     } else {
                //         $value = __('voyager::generic.none');
                //     }
                // }
                // @TODO filter with existing options
                $data->{$row->field} = null; // explode(', ', ($item[$row->field] ?? null));
            } elseif ($row->type == 'multiple_checkbox' && property_exists($row->details, 'options')) {
                // $value = [];
                // if (@count(json_decode(($item[$row->field] ?? null))) > 0) {
                //     foreach (json_decode(($item[$row->field] ?? null)) as $item) {
                //         if (@$row->details->options->{$item}) {
                //             $value = $row->details->options->{$item};
                //         }
                //     }
                // } else {
                //     $value = __('voyager::generic.none');
                // }
                // @TODO filter with existing options
                $data->{$row->field} = null; // explode(', ', ($item[$row->field] ?? null));
            } elseif (($row->type == 'select_dropdown' || $row->type == 'radio_btn') && property_exists($row->details, 'options')) {
                // @TODO filter with existing options
                $data->{$row->field} = null; // $row->details->options->{($item[$row->field] ?? null)} ?? '';
            } elseif ($row->type == 'date' || $row->type == 'timestamp') {
                if (property_exists($row->details, 'format') && !is_null(($item[$row->field] ?? null))) {
                    $data->{$row->field} = \Carbon\Carbon::parse(($item[$row->field] ?? null))->format('YYYY-MM-DD HH:MM:SS');
                } else {
                    $data->{$row->field} = ($item[$row->field] ?? null);
                }
            } elseif ($row->type == 'checkbox') {
                if (property_exists($row->details, 'on') && property_exists($row->details, 'off')) {
                    $data->{$row->field} = ($item[$row->field] ?? null) === $row->details->on;
                } else {
                    $data->{$row->field} = (bool) (int) ($item[$row->field] ?? null);
                }
            } elseif ($row->type == 'color') {
                $data->{$row->field} = ($item[$row->field] ?? null);
            } elseif ($row->type == 'text') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $data->{$row->field} = mb_strlen( ($item[$row->field] ?? null) ) > 200 ? mb_substr(($item[$row->field] ?? null), 0, 200) . ' ...' : ($item[$row->field] ?? null);
                $data->{$row->field} = ($item[$row->field] ?? null);
            } elseif ($row->type == 'password') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $data->{$row->field} = mb_strlen( ($item[$row->field] ?? null) ) > 200 ? mb_substr(($item[$row->field] ?? null), 0, 200) . ' ...' : ($item[$row->field] ?? null);
                // @TODO check if not hash then use bcrypt
                // Ignore if password is not set
                if (($item[$row->field] ?? null)) {
                    $password     = $item[$row->field] ?? null;
                    $passwordInfo = password_get_info($password);
                    if ($passwordInfo['algo']) {
                        $data->{$row->field} = ($item[$row->field] ?? null);
                    } else {
                        $data->{$row->field} = Hash::make($item[$row->field] ?? null);
                    }
                }
            } elseif ($row->type == 'text_area') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $data->{$row->field} = mb_strlen( ($item[$row->field] ?? null) ) > 200 ? mb_substr(($item[$row->field] ?? null), 0, 200) . ' ...' : ($item[$row->field] ?? null);
                $data->{$row->field} = ($item[$row->field] ?? null);
            } elseif ($row->type == 'file' && !empty(($item[$row->field] ?? null))) {
                // $value = [];
                // // view('voyager::multilingual.input-hidden-bread-browse');
                // if (json_decode(($item[$row->field] ?? null)) !== null) {
                //     foreach (json_decode(($item[$row->field] ?? null)) as $file) {
                //         $value = Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '';
                //     }
                // } else {
                //     $value = Storage::disk(config('voyager.storage.disk'))->url(($item[$row->field] ?? null));
                // }
                // @TODO format into json but name is missing
                $data->{$row->field} = null; // implode(', ', $value);
            } elseif ($row->type == 'rich_text_box') {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $data->{$row->field} = mb_strlen( strip_tags(($item[$row->field] ?? null), '<b><i><u>') ) > 200 ? mb_substr(strip_tags(($item[$row->field] ?? null), '<b><i><u>'), 0, 200) . ' ...' : strip_tags(($item[$row->field] ?? null), '<b><i><u>');
                // @TODO newlines may needed to be converted into html
                $data->{$row->field} = ($item[$row->field] ?? null);
            } elseif ($row->type == 'coordinates') {
                // $url = 'https://maps.googleapis.com/maps/api/staticmap?zoom=' . config('voyager.googlemaps.zoom') . '&size=400x100&maptype=roadmap&';
                // foreach ($row['getCoordinates']() as $point) {
                //     $url .= 'markers=color:red%7C' . $point['lat'] . ',' . $point['lng'] . '&center=' . $point['lat'] . ',' . $point['lng'];
                // }
                // $url .= '&key=' . config('voyager.googlemaps.key');
                // // $data->{$row->field} = view('voyager::partials.coordinates-static-image');
                // @TODO parse url and extract the co-ordinates
                $data->{$row->field} = null; // $url;
            } elseif ($row->type == 'multiple_images') {
                // $value = [];
                // $images = json_decode(($item[$row->field] ?? null));
                // if ($images) {
                //     $images = array_slice($images, 0, 3);
                //     foreach ($images as $image) {
                //         if (!filter_var($image, FILTER_VALIDATE_URL)) {
                //             $value = Voyager::image($image);
                //         } else {
                //             $value = $image;
                //         }
                //     }
                // }
                // @TODO format into json but name is missing
                $data->{$row->field} = null; // implode(', ', $value);
            } elseif ($row->type == 'media_picker') {
                // $value = [];

                // if (is_array(($item[$row->field] ?? null))) {
                //     $files = ($item[$row->field] ?? null);
                // } else {
                //     $files = json_decode(($item[$row->field] ?? null));
                // }

                // if ($files) {
                //     if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images) {
                //         foreach (array_slice($files, 0, 3) as $file) {
                //             if (!filter_var($file, FILTER_VALIDATE_URL)) {
                //                 $value = Voyager::image($file);
                //             } else {
                //                 $value = $file;
                //             }
                //         }
                //     } else {
                //         foreach (array_slice($files, 0, 3) as $file) {
                //             $value = $file;
                //         }
                //     }
                //     if (count($files) > 3) {
                //         $value = __('voyager::media.files_more', ['count' => (count($files) - 3)]);
                //     }
                // } elseif (is_array($files) && count($files) == 0) {
                //     $value = trans_choice('voyager::media.files', 0);
                // } elseif (($item[$row->field] ?? null) != '') {
                //     if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images) {
                //         if (!filter_var(($item[$row->field] ?? null), FILTER_VALIDATE_URL)) {
                //             $value = Voyager::image(($item[$row->field] ?? null));
                //         } else {
                //             $value = ($item[$row->field] ?? null);
                //         }
                //     } else {
                //         $value = ($item[$row->field] ?? null);
                //     }
                // } else {
                //     $value = trans_choice('voyager::media.files', 0);
                // }
                // @TODO fixme
                $data->{$row->field} = null; // implode(', ', $value);
            } else {
                // view('voyager::multilingual.input-hidden-bread-browse');
                // $data->{$row->field} = ($item[$row->field] ?? null);
            }
        }

        return $data;
    }
}
