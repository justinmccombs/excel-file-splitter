<?php
/**
 * Created by Justin McCombs.
 * Date: 5/18/15
 * Time: 12:22 PM
 */

namespace ExcelSplit\Http\Controllers;


use ExcelSplit\Services\ExcelSplitterService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ExcelFormController extends Controller {

    public function index()
    {
        return view('index');
    }

    public function store(Request $request, ExcelSplitterService $excelSplitter)
    {
        set_time_limit(0);

        $file = $request->file('file');

        $excelSplitter
            ->setFilePath($file->getRealPath())
            ->setChunkSize($request->get('row_count'))
            ->split();

        return response()->download($excelSplitter->getZipFileName());

    }

}