<?php

namespace Joy\VoyagerBulkUpdate\Events;

use Illuminate\Queue\SerializesModels;

class AllBreadDataTemplateExported
{
    use SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
