<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Shared\Converter;

// Create a new PhpWord object
$phpWord = new PhpWord();

// Add a section to the document
$section = $phpWord->addSection();

// Define the number of rows and columns for the table
$numRows = 5;
$numCols = 3;

// Create a table
$table = $section->addTable(['borderSize' => 6, 'borderColor' => '006699']);

// Add headers to the table
$headerRow = $table->addRow();
for ($col = 1; $col <= $numCols; $col++) {
    $cell = $headerRow->addCell(Converter::inchToTwip(1));
    $cell->addText("Header $col", ['bold' => true]);
}

// Add content rows to the table
for ($row = 1; $row <= $numRows; $row++) {
    $contentRow = $table->addRow(); // Add a new row

    // Add cells to the row
    for ($col = 1; $col <= $numCols; $col++) {
        $cell = $contentRow->addCell(Converter::inchToTwip(1));
        $cell->addText("Row $row, Col $col");
    }
}

// Save the document
$docxFilePath = 'output.docx';
$phpWord->save($docxFilePath);

echo 'DOCX file with table created successfully.';
