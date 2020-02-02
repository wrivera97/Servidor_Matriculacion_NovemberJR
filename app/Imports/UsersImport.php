<?php

namespace App\Imports;

use App\PeriodoAcademico;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new PeriodoAcademico([
            'nombre' => $row[0],
            'estado' => $row[1],
        ]);
    }
}
