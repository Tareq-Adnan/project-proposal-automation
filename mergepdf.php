<?php
require_once('vendor/autoload.php');

use Mpdf\Mpdf;

class PDFMerger
{
    protected $files = [];

    public function addFile($file)
    {
        $this->files[] = $file;
    }

    public function merge($outputFile)
    {
        $mpdf = new Mpdf();

        foreach ($this->files as $file) {
            $mpdf->AddPage();

            // Import page from external PDF
            $pageNumber = 1; // You can adjust the page number as needed
            
            $mpdf->SetSourceFile($file);
            $tplIdx = $mpdf->ImportPage(1);

            // Use the imported page
            $mpdf->UseTemplate($tplIdx);
        }

        // Output the merged PDF to the browser or save it to a file
        $mpdf->Output($outputFile, 'D'); // 'D' means force download
    }
}

$pdfMerger = new PDFMerger();

// Specify PDF files to merge
$pdfFiles = ['output.pdf', 'as.pdf']; // Adjust the file names accordingly

// Add each file to the merger
foreach ($pdfFiles as $file) {
    $pdfMerger->addFile($file);
}

// Merge the PDFs
$pdfMerger->merge('merged.pdf');
