<?php

namespace Joy\VoyagerImport\Http\Controllers;

use Joy\VoyagerImport\Http\Traits\ImportTemplateAction;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as TCGVoyagerBaseController;

class VoyagerBaseController extends TCGVoyagerBaseController
{
    use ImportTemplateAction;
}
