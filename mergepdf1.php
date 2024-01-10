<?php
require_once('vendor/autoload.php');

use setasign\Fpdi\Tcpdf\Fpdi;

class PDFMerger
{
    protected $files = [];

    public function addFile($file)
    {
        $this->files[] = $file;
    }
    public function merge($outputFile)
    {
        $pdf = new Fpdi();

        foreach ($this->files as $file) {
            $pageCount = $pdf->setSourceFile($file);

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $pdf->AddPage();
                $template = $pdf->importPage($pageNumber);
                $size = $pdf->getTemplateSize($template);

                $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']);
            }
        }

        $pdf->Output($outputFile, 'D');
    }
}


