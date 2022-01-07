<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class ExcelExportService implements FromCollection
{
    use Exportable;

    public function __construct(protected Collection $data)
    {
    }

    public function collection(): Collection
    {
        return $this->data;
    }
}
