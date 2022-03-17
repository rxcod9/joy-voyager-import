<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Voyager API Routes
|--------------------------------------------------------------------------
|
| This file is where you may override any of the routes that are included
| with VoyagerBulkUpdate.
|
*/

Route::group(['as' => 'joy-voyager-bulk-update.'], function () {
    // event(new Routing()); @deprecated

    $namespacePrefix = '\\' . config('joy-voyager-bulk-update.controllers.namespace') . '\\';

    // event(new RoutingAfter()); @deprecated
});
