<?php

namespace Joy\VoyagerImport\Http\Controllers;

use Joy\VoyagerImport\Http\Traits\ImportAction;
use Joy\VoyagerImport\Http\Traits\ImportAllAction;
use Joy\VoyagerImport\Http\Traits\ImportAllTemplateAction;
use Joy\VoyagerImport\Http\Traits\ImportTemplateAction;
use Joy\VoyagerCore\Http\Controllers\VoyagerBaseController as BaseVoyagerBaseController;

class VoyagerBaseController extends BaseVoyagerBaseController
{
    use ImportAction;
    use ImportAllAction;
    use ImportTemplateAction;
    use ImportAllTemplateAction;
}
