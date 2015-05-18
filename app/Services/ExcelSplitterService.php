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

    /**
     * Sets the path of the file to process
     *
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * Sets the chunk size, or how many rows should be in each output file
     *
     * @param $chunkSize
     * @return $this
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
        return $this;
    }

    /**
     * Split the file up into smaller chunks.
     *
     * @throws \Exception
     */
    public function split()
    {
        if ( ! $this->filePath )
            throw new \Exception('Cannot have a blank file path.');

        // Load in data, split into pages
        $this->loadDataIntoPages();

        // Export the pages to separate files
        $this->splitPagesToFiles();

        // Zip files together, delete temporary files
        $this->zipFiles();

        return true;
    }

    /**
     * Exports $this->pages into separate files
     */
    protected function splitPagesToFiles()
    {
        // Output each page to a file
        $pageNumber = 1;

        // Loop through pages and create an xls file for each page
        $this->pages->each(function($pageData) use(&$pageNumber) {
            $filename = $this->uuid.'-'.$pageNumber;
            Excel::create($filename, function($excel) use($pageData) {
                $excel->sheet('Sheet 1', function($sheet) use($pageData)  {
                    $sheet->fromArray($pageData);
                });
            })->store('xls', $this->getTempFilePath());

            // Push filename to filesToZip collection
            $this->filesToZip->push($this->getTempFilePath().$filename.'.xls');

            // Increment page number for the next filename
            $pageNumber++;
        });
    }

    /**
     * Loads the file, and splits the data into $this->pages
     */
    protected function loadDataIntoPages()
    {
        // Load Excel File
        Excel::load($this->filePath, function(LaravelExcelReader $reader) {
            $this->data = new Collection($reader->toArray());
        });

        // Create Pages collection based on set chunkSize
        $this->data->chunk($this->chunkSize)->each(function($results) {
            $this->pages->push($results->toArray());
        });
    }

    /**
     * Zips the files in $this->filesToZip, saves the zip file,
     * and deletes the temporary files.
     */
    protected function zipFiles()
    {
        // Zip Files
        $zipper = new Zipper;
        $zipper->make($this->getZipFileName())      // Set zipfile name
        ->add($this->filesToZip->toArray())     // Set files to zip
        ->close();                              // Compress files and save

        // Delete Temporary Files
        \File::deleteDirectory($this->getTempFilePath());
    }

    /**
     * Return the full path and filename of the zip file to save and return.
     *
     * @return string
     */
    public function getZipFileName()
    {
        return storage_path('zipped/'.$this->uuid.'.zip');
    }

    /**
     * Return the temporary path where the split up sheets
     * will sit until they're zipped up.
     *
     * @return string
     */
    protected function getTempFilePath()
    {
        return storage_path('temp/'.$this->uuid.'/');
    }

    /**
     * Generates a Uuid, then returns the first 8 characters so our
     * file names remain short and manageable
     *
     * @return string
     */
    protected function generateUuid()
    {
        $uuid = Uuid::uuid4();
        $uuidArray = explode('-', $uuid->toString());
        return $uuidArray[0];
    }

}