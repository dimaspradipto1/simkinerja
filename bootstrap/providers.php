<?php

use App\Providers\AppServiceProvider;
use RealRashid\SweetAlert\SweetAlertServiceProvider;
use Yajra\DataTables\DataTablesServiceProvider;



return [
    AppServiceProvider::class,
    DataTablesServiceProvider::class,
    SweetAlertServiceProvider::class,

];
