<?php
require_once 'functions.php';
requireLogin();

// Get export parameters
$format = $_GET['format'] ?? 'csv';
$type = $_GET['type'] ?? 'all';
$date_range = $_GET['date_range'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Build date condition
$date_condition = '';
if ($date_range !== 'all') {
    switch ($date_range) {
        case 'today':
            $date_condition = "WHERE DATE(date_added) = CURDATE()";
            break;
        case 'week':
            $date_condition = "WHERE YEARWEEK(date_added) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $date_condition = "WHERE YEAR(date_added) = YEAR(CURDATE()) AND MONTH(date_added) = MONTH(CURDATE())";
            break;
        case 'year':
            $date_condition = "WHERE YEAR(date_added) = YEAR(CURDATE())";
            break;
        case 'custom':
            if ($start_date && $end_date) {
                $date_condition = "WHERE date_added BETWEEN '$start_date' AND '$end_date'";
            }
            break;
    }
}

// Get data based on type
function getData($type, $date_condition) {
    global $conn;
    $data = [];
    
    if ($type === 'all' || $type === 'income') {
        $sql = "SELECT i.*, u.username, u.email 
                FROM income i 
                LEFT JOIN users u ON i.user_id = u.id 
                $date_condition 
                ORDER BY i.date_added DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'Income';
            $data[] = $row;
        }
    }
    
    if ($type === 'all' || $type === 'expenses') {
        $sql = "SELECT e.*, u.username, u.email 
                FROM expenses e 
                LEFT JOIN users u ON e.user_id = u.id 
                $date_condition 
                ORDER BY e.date_added DESC";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'Expense';
            $data[] = $row;
        }
    }
    
    return $data;
}

$data = getData($type, $date_condition);

// Export based on format
switch ($format) {
    case 'csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="financial_data_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, ['Type', 'Title', 'Amount', 'Category/Source', 'Date', 'User', 'Email']);
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, [
                $row['type'],
                $row['title'],
                $row['amount'],
                $row['type'] === 'Income' ? $row['source'] : $row['category'],
                $row['date_added'],
                $row['username'],
                $row['email']
            ]);
        }
        
        fclose($output);
        break;
        
    case 'excel':
        require 'vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed
        
        use PhpOffice\PhpSpreadsheet\Spreadsheet;
        use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add headers
        $sheet->setCellValue('A1', 'Type');
        $sheet->setCellValue('B1', 'Title');
        $sheet->setCellValue('C1', 'Amount');
        $sheet->setCellValue('D1', 'Category/Source');
        $sheet->setCellValue('E1', 'Date');
        $sheet->setCellValue('F1', 'User');
        $sheet->setCellValue('G1', 'Email');
        
        // Add data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A'.$row, $item['type']);
            $sheet->setCellValue('B'.$row, $item['title']);
            $sheet->setCellValue('C'.$row, $item['amount']);
            $sheet->setCellValue('D'.$row, $item['type'] === 'Income' ? $item['source'] : $item['category']);
            $sheet->setCellValue('E'.$row, $item['date_added']);
            $sheet->setCellValue('F'.$row, $item['username']);
            $sheet->setCellValue('G'.$row, $item['email']);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="financial_data_' . date('Y-m-d') . '.xlsx"');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        break;
        
    case 'pdf':
        require 'vendor/autoload.php'; // Make sure you have TCPDF installed
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Expense Tracker');
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Financial Data Export');
        
        $pdf->AddPage();
        
        // Add title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Financial Data Export', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Add table headers
        $pdf->SetFont('helvetica', 'B', 11);
        $headers = ['Type', 'Title', 'Amount', 'Category/Source', 'Date', 'User', 'Email'];
        foreach ($headers as $header) {
            $pdf->Cell(27, 7, $header, 1, 0, 'C');
        }
        $pdf->Ln();
        
        // Add data
        $pdf->SetFont('helvetica', '', 10);
        foreach ($data as $row) {
            $pdf->Cell(27, 6, $row['type'], 1);
            $pdf->Cell(27, 6, $row['title'], 1);
            $pdf->Cell(27, 6, '$' . number_format($row['amount'], 2), 1);
            $pdf->Cell(27, 6, $row['type'] === 'Income' ? $row['source'] : $row['category'], 1);
            $pdf->Cell(27, 6, date('Y-m-d', strtotime($row['date_added'])), 1);
            $pdf->Cell(27, 6, $row['username'], 1);
            $pdf->Cell(27, 6, $row['email'], 1);
            $pdf->Ln();
        }
        
        $pdf->Output('financial_data_' . date('Y-m-d') . '.pdf', 'D');
        break;
}
?>