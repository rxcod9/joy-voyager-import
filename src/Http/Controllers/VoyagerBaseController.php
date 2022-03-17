<?php

namespace Joy\VoyagerBulkUpdate\Http\Controllers;

use Joy\VoyagerBulkUpdate\Http\Traits\ImportAction;
use Joy\VoyagerBulkUpdate\Http\Traits\ImportAllAction;
use Joy\VoyagerBulkUpdate\Http\Traits\ImportAllTemplateAction;
use Joy\VoyagerBulkUpdate\Http\Traits\ImportTemplateAction;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as TCGVoyagerBaseController;

class VoyagerBaseController extends TCGVoyagerBaseController
{
    use ImportAction;
    use ImportAllAction;
    use ImportTemplateAction;
    use ImportAllTemplateAction;
}
