<?php
require 'vendor/autoload.php';
require 'mergepdf1.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
use Smalot\PdfParser\Parser;

$spreadsheet = IOFactory::load('C:\Users\BS1065\Downloads\Estimation for Nazmc_v2.0.xlsx');

$mainTable = '';
$indexes = [4, 3, 2];
$const = '
<h5 style="color:#2ACAEA;font-size:25px">2. Scope of Work</h5>
<h6 style="font-size:18px">2.1 Time Estimation</h6>
<p>The assumption from our understanding of the requirement is below</p>
';
$mainTable .= $const;

for ($i = 0; $i < count($indexes); $i++) {
    $mainTable .= get_excel_data($spreadsheet, $indexes[$i], $i);
}
// echo $mainTable . PHP_EOL;
file_put_contents('output.html', $mainTable);
// ['orientation' => 'L'] for landscape

$mpdf = new Mpdf();
$mpdf->SetHTMLHeader(get_header());
$mpdf->SetTopMargin(25);
$mpdf->WriteHTML($mainTable);
$mpdf->Output('output.pdf', 'F');

//// Generating Table of Content.
$tableOfContent = new Mpdf();
$tableOfContent->SetHTMLHeader(get_header());
$tableOfContent->WriteHTML(get_contents());
$tableOfContent->SetTopMargin(25);
$tableOfContent->Output('tableOfContent.pdf', 'F');

// Generating Cover Page
$data = get_excel_data($spreadsheet, 0, 8, true);

$stylesheet = file_get_contents('style.css');
$cover = new Mpdf();
$cover->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$cover->WriteHTML($data, \Mpdf\HTMLParserMode::HTML_BODY);
// $cover->WriteHTML($data);
$cover->Output('cover.pdf', 'F');

// Generating Terms Page
$termsdata = get_excel_data($spreadsheet, 6, 8, false, true);

$stylesheet = file_get_contents('style.css');
$terms = new Mpdf();
$terms->SetHTMLHeader(get_header());
$terms->SetTopMargin(25);
$terms->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$terms->WriteHTML($termsdata, \Mpdf\HTMLParserMode::HTML_BODY);
// $cover->WriteHTML($data);
$terms->Output('terms.pdf', 'F');
// echo $termsdata;

// Generate Table of Contents



$pdfMerger = new PDFMerger();

// Specify PDF files to merge
$pdfFiles = ['cover.pdf', 'tableOfContent.pdf', 'pre_pages.pdf', 'output.pdf', 'terms.pdf', 'post_pages.pdf']; // Adjust the file names accordingly
// Add each file to the merger
foreach ($pdfFiles as $file) {
    $pdfMerger->addFile($file);
}
// Merge the PDFs
$pdfMerger->merge('proposal.pdf');

function get_excel_data($spreadsheet, $index, $i = null, $cover = false, $terms = false)
{
    $mainTable = "";
    $worksheet = $spreadsheet->getSheet($index); //$spreadsheet-getSheet(2);
    //$worksheet = $spreadsheet->getSheetByName('Scope of Work');
    $highestRow = $worksheet->getHighestDataRow();
    $highestColumn = $worksheet->getHighestDataColumn();
    $highestColumnIndex = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

    // Determine non-empty rows
    $nonEmptyRows = [];
    for ($row = 1; $row <= $highestRow; ++$row) {
        $isEmptyRow = true;
        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
            $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if ($cellValue !== null && $cellValue !== '') {
                $isEmptyRow = false;
                break; // No need to check other cells in the same row
            }
        }
        if (!$isEmptyRow) {
            $nonEmptyRows[] = $row;
        }
    }

    // Determine non-empty columns
    $nonEmptyColumns = [];
    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
        $isEmptyColumn = true;
        for ($row = 1; $row <= $highestRow; ++$row) {
            $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if ($cellValue !== null && $cellValue !== '') {
                $isEmptyColumn = false;
                break; // No need to check other cells in the same column
            }
        }
        if (!$isEmptyColumn) {
            $nonEmptyColumns[] = $col;
        }
    }

    // Output non-empty rows and columns

    if ($cover) {
        $title = [
            '<div class="text-div"><p class="proposal"><span class="prop">PROPOSAL:</span>',
            '</p><p class="prop2"><span class="proposedto">PROPOSED TO:</span>',
            '</p><p class="prop3"><span class="company">COMPANY REPRESENTATIVE/S:</span>',
            '<span class="sdate">SUBMISSION DATE:</span>',
            "<span class='vdate'>VALID TILL:</span>"
        ];
        $i = 0;
        $data = "<div class='data'>";
        $data .= "<img src='bg-logo.png' class='img'>";
        foreach ($nonEmptyRows as $key => $row) {
            // $data .= "<tr>";
            foreach ($nonEmptyColumns as $colkey => $col) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                if ($colkey == 0)
                    continue;
                else if ($i == 3 || $i == 4) {
                    $dateValue = $cellValue;
                    $dateTimeObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    $formattedDate = $dateTimeObject->format('Y-m-d');
                    $data .= $title[$key] . " <span class='value'>" . $formattedDate . "</span>";
                } else {
                    $data .= $title[$key] . " <span class='value'>" . $cellValue . "</span>";
                }
                $i++;
            }
            $data .= "  <br>";
        }

        $data .= "</p></div><img src='bg-logo2.png' class='img2'></div>";
        return $data;
    }
    if ($terms) {
        $data = "<h2  class='title'>4. Payment Terms</h2>";
        $data .= "<ul>";
        foreach ($nonEmptyRows as $key => $row) {
            // $data .= "<tr>";
            foreach ($nonEmptyColumns as $colkey => $col) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                //  echo $key.".$colkey.".$cellValue."<br>";
                if ($cellValue) {
                    if ($cellValue == "Payment Terms" || $cellValue == "Terms & Conditions")
                        continue;
                    // if(($key==0 && $colkey== 0) || ($key==3 && $colkey==0)) continue;
                    if ($key == 1)
                        $data .= "<li><b>Payment Method</b>" . "<ul><li>" . $cellValue . $key . "</li></ul></li>";
                    else if ($key == 2)
                        $data .= "<li><b>(VAT & TAX)</b>" . "<ul><li>" . $cellValue . "</li></ul></li></ul><h2  class='title'>5. Terms & Conditions</h2><ul>";
                    else if ($key == 5)
                        $data .= "<li><b>Non-Disclosure Understanding</b>" . "<ul><li>" . $cellValue . "</li></ul></li>";
                    else if ($key == 6)
                        $data .= "<li><b>Offer Validity</b>" . "<ul><li>" . $cellValue . "</li></ul></li>";
                    else
                        $data .= "<li>" . $cellValue . "</li></li>";
                }

            }
        }

        $data .= "</ul>";
        return $data;
    }
    $data = "<table border='1' cellpadding='4' style='width: 100%;border-collapse:collapse;text-align:center;'>";
    foreach ($nonEmptyRows as $row) {
        $data .= "<tr>";
        foreach ($nonEmptyColumns as $col) {
            $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if ($row === 1) {
                $data .= "<th style='background-color:#006AB5;color:white'>$cellValue</th>";
            } else {
                $data .= "<td>$cellValue</td>";
            }
        }
        $data .= "</tr>";
    }
    $data .= '</table>' . PHP_EOL;
    $mainTable .= $data;

    // Adding Section Title for different tables.
    switch ($i) {
        case 0:
            $mainTable .= '<h6>2.2 Timeline</h6>';
            break;
        case 1:
            $mainTable .= '<h6 style="font-size:25px;color:#2ACAEA">3. Pricing</h6>';
            $mainTable .= '<p>3.1 Development Cost: </p>';
            break;
    }
    return $mainTable;
}

function get_header()
{
    $headerContent = '<div style="position:absolute;top:0;left:0;background-color:#f3f3f3;padding:10px;margin:10px 0px">
        <table style="width: 100%;border-collapse:collapse;margin:0px 40px;">
            <tr>
                <td style="text-align: left;">
                <img src="logo.png" style="width:40px" >
                </td>
                <td style="text-align: right;"><b>Brain Station 23 Ltd.</b> | www.brainstation-23.com</td>
            </tr>
        </table>
    </div>
';
    return $headerContent;
}

function get_pages()
{
    $assArray = [];
    $keyword = ['Scope of Work', 'Time Estimation', 'Timeline', 'Pricing', 'Development Cost'];
    $parser = new Parser();

    // Parse the PDF file
    $pdf = $parser->parseFile('output.pdf');

    // Get the total number of pages in the PDF
    $totalPages = count($pdf->getPages());
    foreach ($keyword as $key) {
        for ($pageNumber = 0; $pageNumber < $totalPages; $pageNumber++) {

            // Extract text from the current page
            $text = $pdf->getPages()[$pageNumber]->getText();
            //echo $text."<br>";
            // Check if the keyword is present in the extracted text
            if (strpos($text, $key) !== false) {

                $assArray[$key] = $pageNumber + 8;
            }
        }
    }
    // Iterate through each page and search for the keyword
    $assArray['Payment Terms'] = $assArray['Development Cost'] + 1;
    $assArray['Terms & Conditions'] = $assArray['Payment Terms'];
    return $assArray;
}

function get_contents()
{

    $content = "<div style='padding:20px;'><h1 style='color:#2ACAEA'> Table of Contents</h1>";

    $content .= '<table style="width:100%">';
    $content .= '
        <tr><td>1. <b style="color:#2ACAEA">About Brain Station 23 Ltd.</b></td><td> 3</td></tr>
        <tr><td style="padding-left:10px">1.1. Industries we worked with</td><td> 3</td></tr>
        <tr><td style="padding-left:10px">1.2. Awards & Recognitions</td><td> 3</td></tr>
        <tr><td style="padding-left:10px">1.3. Business Information</td><td> 4</td></tr>
        <tr><td style="padding-left:10px">1.4. Geographical Reach & Market Presence</td><td> 4</td></tr>
        <tr><td style="padding-left:10px">1.5. Key Clients</td><td> 5</td></tr>
        <tr><td style="padding-left:10px">1.6. Technical Expertise</td><td> 6</td></tr>';
    $pages = get_pages();
    $keyword = ['Scope of Work', 'Time Estimation', 'Timeline', ' Pricing', 'Development Cost'];
    $i = 1;
    $j = 1;
    foreach ($pages as $key => $pageNo) {
        if ($key === 'Scope of Work' || $key === 'Pricing' || $key === 'Terms & Conditions' || $key === 'Payment Terms') {
            ++$i;
            $j = 1;
            $content .= "<tr><td>$i. <b style='color:#2ACAEA'>$key</b></td> <td>$pageNo</td></tr>";
        } else {
            $content .= "<tr><td style='padding-left:10px'>$i.$j. $key</td> <td>$pageNo</td></tr>";
            $j++;
        }
    }
    $content .= "</table></div>";
    return $content;
}