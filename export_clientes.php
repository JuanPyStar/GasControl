<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$sql = "SELECT id_cliente, nombre, documento_cliente, placa_vehiculo, telefono, millas FROM clientes ORDER BY nombre ASC";
$stmt = $db->prepare($sql);
$stmt->execute();

$clientes = [];
$total_millas = 0;
$cantidad_clientes = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $clientes[] = $row;
    $total_millas += $row['millas'];
    $cantidad_clientes++;
}
$promedio_millas = $cantidad_clientes > 0 ? round($total_millas / $cantidad_clientes, 2) : 0;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Resumen ejecutivo
$sheet->setCellValue('A1', 'REPORTE DE CLIENTES');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->setCellValue('A2', 'Fecha de generación:');
$sheet->setCellValue('B2', date('Y-m-d H:i:s'));
$sheet->setCellValue('A3', 'Cantidad de clientes:');
$sheet->setCellValue('B3', $cantidad_clientes);
$sheet->setCellValue('A4', 'Total de millas acumuladas:');
$sheet->setCellValue('B4', $total_millas);
$sheet->setCellValue('A5', 'Promedio de millas por cliente:');
$sheet->setCellValue('B5', $promedio_millas);

// Encabezados de la tabla
$startRow = 7;
$sheet->setCellValue('A'.$startRow, 'ID')
      ->setCellValue('B'.$startRow, 'Nombre')
      ->setCellValue('C'.$startRow, 'Documento')
      ->setCellValue('D'.$startRow, 'Placa')
      ->setCellValue('E'.$startRow, 'Teléfono')
      ->setCellValue('F'.$startRow, 'Millas');
$sheet->getStyle('A'.$startRow.':F'.$startRow)->getFont()->setBold(true);

// Datos de clientes
$rowNum = $startRow + 1;
foreach ($clientes as $row) {
    $sheet->setCellValue('A'.$rowNum, $row['id_cliente'])
          ->setCellValue('B'.$rowNum, $row['nombre'])
          ->setCellValue('C'.$rowNum, $row['documento_cliente'])
          ->setCellValue('D'.$rowNum, $row['placa_vehiculo'])
          ->setCellValue('E'.$rowNum, $row['telefono'])
          ->setCellValue('F'.$rowNum, $row['millas']);
    $rowNum++;
}

// Totales y promedio al final de la tabla
$sheet->setCellValue('E'.$rowNum, 'Totales');
$sheet->setCellValue('F'.$rowNum, $total_millas);
$sheet->getStyle('E'.$rowNum.':F'.$rowNum)->getFont()->setBold(true)->getColor()->setARGB('FF0000');
$rowNum++;
$sheet->setCellValue('E'.$rowNum, 'Promedio');
$sheet->setCellValue('F'.$rowNum, $promedio_millas);
$sheet->getStyle('E'.$rowNum.':F'.$rowNum)->getFont()->setBold(true);

// Autoajustar ancho de columnas
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Bordes para la tabla
$lastDataRow = $rowNum;
$sheet->getStyle('A'.$startRow.':F'.$lastDataRow)
    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Descargar el archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="clientes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>