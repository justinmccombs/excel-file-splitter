<?php
/**
 * Created by Justin McCombs.
 * Date: 5/18/15
 * Time: 12:35 PM
 */

namespace ExcelSplit\Services;

use Chumper\Zipper\Zipper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Ramsey\Uuid\Uuid;
use \Excel;

class ExcelSplitterService {

    protected $filePath;

    protected $zipFilePath;

    protected $filesToZip;

    protected $uuid;

    protected $chunkSize = 100;

    protected $headerCount = 0;

    protected $outputDir;

    protected $excelObj;

    /**
     * @var Collection
     */
    protected $pages;

    /**
     * @var Collection
     */
    protected $data;

    public function __construct()
    {
        $this->pages = new Collection;
        $this->data = new Collection;
        $this->filesToZip = new Collection;
        $this->uuid = $this->generateUuid();
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
        return $this;
    }

    public function setHeaderCount($headerCount)
    {
        $this->headerCount = $headerCount;
        return $this;
    }

    public function split()
    {
        if ( ! $this->filePath )
            throw new \Exception('Cannot have a blank file path.');

        // Load Excel File
        Excel::load($this->filePath, function(LaravelExcelReader $reader) {
            $this->data = new Collection($reader->toArray());
        });

        // Create Pages collection based on set chunkSize
        $this->data->chunk($this->chunkSize)->each(function($results) {
            $this->pages->push($results->toArray());
        });

        // Output each page to a file
        $pageNumber = 1;
        $this->pages->each(function($pageData) use(&$pageNumber) {
            $filename = $this->uuid.'-'.$pageNumber;
            Excel::create($filename, function($excel) use($pageData) {
                $excel->sheet('Sheet 1', function($sheet) use($pageData)  {
                    $sheet->fromArray($pageData);
                });
            })->store('xls', $this->getTempFilePath());
            $this->filesToZip->push($this->getTempFilePath().$filename.'.xls');
            $pageNumber++;
        });

        // Zip Files
        $zipper = new Zipper;
        $zipper->make($this->getZipFileName())->add($this->filesToZip->toArray())->close();

        // Delete Temporary Files
//        $this->filesToZip->each(function($filePath) use(&$zipper) {
//            \File::delete($filePath);
//        });
//        \File::deleteDirectory($this->getTempFilePath());
//
    }

    public function download()
    {

    }

    public function getZipFileName()
    {
        return storage_path('zipped/'.$this->uuid.'.zip');
    }

    protected function getTempFilePath()
    {
        return storage_path('temp/'.$this->uuid.'/');
    }

    protected function generateUuid()
    {
        $uuid = Uuid::uuid4();
        $uuidArray = explode('-', $uuid->toString());
        return $uuidArray[0];
    }

}