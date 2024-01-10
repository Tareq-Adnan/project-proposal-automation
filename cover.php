<?php
require 'vendor/autoload.php';
require 'mergepdf1.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;

$spreadsheet = IOFactory::load('C:\Users\BS1065\Downloads\Estimation for Impact Academy v1.0.xlsx');
$mainTable = '';
$indexes = [3, 2, 1];
$const = '
<h5 style="color:#2ACAEA;font-size:25px">2. Scope of Work</h5>
<h6 style="font-size:18px">2.1 Time Estimation</h6>
<p>The assumption from our understanding of the requirement is below</p>
';
$mainTable .= $const;

for ($i = 0; $i < count($indexes); $i++) {
   $mainTable.= get_excel_data($spreadsheet,$indexes[$i],$i);
}
//echo $mainTable . PHP_EOL;
file_put_contents('output.html', $mainTable);
// ['orientation' => 'L'] for landscape

$mpdf = new Mpdf();
$mpdf->SetHTMLHeader(get_header());
$mpdf->SetTopMargin(25);
$mpdf->WriteHTML($mainTable);
$mpdf->Output('output.pdf', 'F');

$data=get_excel_data($spreadsheet,0,8,true);

$stylesheet = file_get_contents('style.css');
$cover = new Mpdf();
$cover->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
$cover->WriteHTML($data,\Mpdf\HTMLParserMode::HTML_BODY);
// $cover->WriteHTML($data);
$cover->Output('cover.pdf', 'F');

$pdfMerger = new PDFMerger();

// Specify PDF files to merge
$pdfFiles = ['pre_pages.pdf', 'output.pdf','post_pages.pdf']; // Adjust the file names accordingly
// Add each file to the merger
foreach ($pdfFiles as $file) {
    $pdfMerger->addFile($file);
}
// Merge the PDFs
// $pdfMerger->merge('tareq.pdf');

function get_excel_data ($spreadsheet,$index,$i=null,$cover=false) {
    $mainTable="";
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

    if($cover) {
        $title=['<div class="text-div"><p class="proposal"><span class="prop">PROPOSAL:</span>',
        '</p><p class="prop2"><span class="proposedto">PROPOSED TO:</span>', 
        '</p><p class="prop3"><span class="company">COMPANY REPRESENTATIVE/S:</span>',
        '<span class="sdate">SUBMISSION DATE:</span>',
        "<span class='vdate'>VALID TILL:</span>" ];
        $i=0;
        $data="<div class='data'>";
        $data.="<img src='bg-logo.png' class='img'>";
        foreach ($nonEmptyRows as $key => $row) {
           // $data .= "<tr>";
            foreach ($nonEmptyColumns as $col) {
                $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                if ($i == 3 || $i == 4) { 
                    $dateValue = $cellValue;
                    $dateTimeObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    $formattedDate = $dateTimeObject->format('Y-m-d'); 
                    $data .= $title[$key] . " <span class='value'>" . $formattedDate . "</span>";
                } else {
                    $data .= $title[$key] . " <span class='value'>" . $cellValue . "</span>";
                }
                $i++;
            }
            $data.="  <br>";
        }
        
        $data.="</p></div><img src='bg-logo2.png' class='img2'></div>";
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

function get_header(){
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