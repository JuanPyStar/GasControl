<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$sql = "SELECT v.id_venta, v.fecha_hora, c.nombre AS cliente, co.tipo AS combustible, v.galones_vendidos, v.total_pagado
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
        LEFT JOIN combustibles co ON v.id_combustible = co.id_combustible
        ORDER BY v.fecha_hora DESC";
$stmt = $db->prepare($sql);
$stmt->execute();

$ventas = [];
$total_galones = 0;
$total_ventas = 0;
$cantidad_ventas = 0;
$fechas = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ventas[] = $row;
    $total_galones += $row['galones_vendidos'];
    $total_ventas += $row['total_pagado'];
    $fechas[] = $row['fecha_hora'];
    $cantidad_ventas++;
}

$promedio_galones = $cantidad_ventas > 0 ? round($total_galones / $cantidad_ventas, 2) : 0;
$promedio_ventas = $cantidad_ventas > 0 ? round($total_ventas / $cantidad_ventas, 2) : 0;
$fecha_primera = $cantidad_ventas > 0 ? min($fechas) : '';
$fecha_ultima = $cantidad_ventas > 0 ? max($fechas) : '';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Resumen
$sheet->setCellValue('A1', 'REPORTE DE VENTAS');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->setCellValue('A2', 'Fecha de generación:');
$sheet->setCellValue('B2', date('Y-m-d H:i:s'));
$sheet->setCellValue('A3', 'Total de ventas:');
$sheet->setCellValue('B3', $cantidad_ventas);
$sheet->setCellValue('A4', 'Total galones vendidos:');
$sheet->setCellValue('B4', $total_galones);
$sheet->setCellValue('A5', 'Total vendido ($):');
$sheet->setCellValue('B5', $total_ventas);
$sheet->setCellValue('A6', 'Promedio galones por venta:');
$sheet->setCellValue('B6', $promedio_galones);
$sheet->setCellValue('A7', 'Promedio venta ($):');
$sheet->setCellValue('B7', $promedio_ventas);
$sheet->setCellValue('A8', 'Fecha primera venta:');
$sheet->setCellValue('B8', $fecha_primera);
$sheet->setCellValue('A9', 'Fecha última venta:');
$sheet->setCellValue('B9', $fecha_ultima);

// Encabezados de la tabla
$startRow = 11;
$sheet->setCellValue('A'.$startRow, 'ID')
      ->setCellValue('B'.$startRow, 'Fecha')
      ->setCellValue('C'.$startRow, 'Cliente')
      ->setCellValue('D'.$startRow, 'Combustible')
      ->setCellValue('E'.$startRow, 'Galones')
      ->setCellValue('F'.$startRow, 'Total');
$sheet->getStyle('A'.$startRow.':F'.$startRow)->getFont()->setBold(true);

// Datos de ventas
$rowNum = $startRow + 1;
foreach ($ventas as $row) {
    $sheet->setCellValue('A'.$rowNum, $row['id_venta'])
          ->setCellValue('B'.$rowNum, $row['fecha_hora'])
          ->setCellValue('C'.$rowNum, $row['cliente'])
          ->setCellValue('D'.$rowNum, $row['combustible'])
          ->setCellValue('E'.$rowNum, $row['galones_vendidos'])
          ->setCellValue('F'.$rowNum, $row['total_pagado']);
    $rowNum++;
}

// Totales y promedios al final de la tabla
$sheet->setCellValue('D'.$rowNum, 'Totales');
$sheet->setCellValue('E'.$rowNum, $total_galones);
$sheet->setCellValue('F'.$rowNum, $total_ventas);
$sheet->getStyle('D'.$rowNum.':F'.$rowNum)->getFont()->setBold(true)->getColor()->setARGB('FF0000');
$rowNum++;
$sheet->setCellValue('D'.$rowNum, 'Promedio');
$sheet->setCellValue('E'.$rowNum, $promedio_galones);
$sheet->setCellValue('F'.$rowNum, $promedio_ventas);
$sheet->getStyle('D'.$rowNum.':F'.$rowNum)->getFont()->setBold(true);

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
header('Content-Disposition: attachment;filename="ventas.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>