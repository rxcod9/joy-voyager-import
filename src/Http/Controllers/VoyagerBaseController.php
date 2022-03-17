<?php

namespace Joy\VoyagerImport\Http\Controllers;

use Joy\VoyagerImport\Http\Traits\ImportAction;
use Joy\VoyagerImport\Http\Traits\ImportAllAction;
use Joy\VoyagerImport\Http\Traits\ImportAllTemplateAction;
use Joy\VoyagerImport\Http\Traits\ImportTemplateAction;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as TCGVoyagerBaseController;

class VoyagerBaseController extends TCGVoyagerBaseController
{
    use ImportAction;
    use ImportAllAction;
    use ImportTemplateAction;
    use ImportAllTemplateAction;
}
