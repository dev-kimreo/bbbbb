<?php

namespace App\Http\Controllers;

use App\Http\Requests\Export\ExcelRequest;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    /**
     * @param ExcelRequest $request
     * @return BinaryFileResponse
     */
    public function excel(ExcelRequest $request): BinaryFileResponse
    {
        $data = collect(json_decode($request->input('data'), true));

        if($request->input('header')) {
            $data->prepend(json_decode($request->input('header'), true));
        }

        return (new ExcelExportService($data))->download($request->input('file_name'));
    }
}
