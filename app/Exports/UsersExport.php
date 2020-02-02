<?php

namespace App\Exports;

use App\Malla;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;


class UsersExport implements FromQuery
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;

    public function collection()
    {
        return Malla::all();
    }
}
