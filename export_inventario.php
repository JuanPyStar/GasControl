<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$sql = "SELECT id_combustible, tipo, stock_actual, precio_galon FROM combustibles ORDER BY tipo ASC";
$stmt = $db->prepare($sql);
$stmt->execute();

$productos = [];
$total_stock = 0;
$cantidad_productos = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productos[] = $row;
    $total_stock += $row['stock_actual'];
    $cantidad_productos++;
}
$promedio_stock = $cantidad_productos > 0 ? round($total_stock / $cantidad_productos, 2) : 0;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Resumen ejecutivo
$sheet->setCellValue('A1', 'REPORTE DE INVENTARIO');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->setCellValue('A2', 'Fecha de generación:');
$sheet->setCellValue('B2', date('Y-m-d H:i:s'));
$sheet->setCellValue('A3', 'Cantidad de productos:');
$sheet->setCellValue('B3', $cantidad_productos);
$sheet->setCellValue('A4', 'Stock total:');
$sheet->setCellValue('B4', $total_stock);
$sheet->setCellValue('A5', 'Promedio de stock por producto:');
$sheet->setCellValue('B5', $promedio_stock);

// Encabezados de la tabla
$startRow = 7;
$sheet->setCellValue('A'.$startRow, 'ID')
      ->setCellValue('B'.$startRow, 'Tipo')
      ->setCellValue('C'.$startRow, 'Stock')
      ->setCellValue('D'.$startRow, 'Precio');
$sheet->getStyle('A'.$startRow.':D'.$startRow)->getFont()->setBold(true);

// Datos de productos
$rowNum = $startRow + 1;
foreach ($productos as $row) {
    $sheet->setCellValue('A'.$rowNum, $row['id_combustible'])
          ->setCellValue('B'.$rowNum, $row['tipo'])
          ->setCellValue('C'.$rowNum, $row['stock_actual'])
          ->setCellValue('D'.$rowNum, $row['precio_galon']);
    $rowNum++;
}

// Totales y promedio al final de la tabla
$sheet->setCellValue('B'.$rowNum, 'Totales');
$sheet->setCellValue('C'.$rowNum, $total_stock);
$sheet->getStyle('B'.$rowNum.':C'.$rowNum)->getFont()->setBold(true)->getColor()->setARGB('FF0000');
$rowNum++;
$sheet->setCellValue('B'.$rowNum, 'Promedio');
$sheet->setCellValue('C'.$rowNum, $promedio_stock);
$sheet->getStyle('B'.$rowNum.':C'.$rowNum)->getFont()->setBold(true);

// Autoajustar ancho de columnas
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Bordes para la tabla
$lastDataRow = $rowNum;
$sheet->getStyle('A'.$startRow.':D'.$lastDataRow)
    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Descargar el archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="inventario.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>