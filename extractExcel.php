<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;

$spreadsheet = IOFactory::load('a.xlsx');

 $worksheet = $spreadsheet->getSheet(2);



$highestRow = $worksheet->getHighestDataRow(); // Gets the highest row number
$highestColumn = $worksheet->getHighestDataColumn(); // Gets the highest column letter

// Convert the highest column letter to a number (e.g., 'Z' -> 26)
$highestColumnIndex = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

$data = "";
$data .= "<table border='1' cellpadding='4' style='border-collapse:collapse;text-align:center'>";
for ($row = 1; $row <= 1; ++$row) {
    $data .= "<tr>";
    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        if ($cellValue) {
            $data .= "<th>$cellValue</th>";
        }
    }
    $data .= "</tr>";

}
for ($row = 2; $row <= $highestRow; ++$row) {
    $data .= "<tr>";
    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
        if ($cellValue) {
            $data .= "<td>$cellValue</td>";
        }
    }
    $data .= "</tr>";
}
$data .= '</table>' . PHP_EOL;
file_put_contents('output.html', $data);

$mpdf = new Mpdf(['orientation' => 'L']);
$mpdf->WriteHTML($data);
$mpdf->Output('output.pdf', 'F');



