<?php
require_once 'functions.php';

// Get filter parameters
$date_range = $_GET['date_range'] ?? 'month';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$category = $_GET['category'] ?? 'all';
$chart_range = $_GET['chart_range'] ?? 12;
$chart_type = $_GET['chart_type'] ?? 'bar';

// User filter parameters
$user_type = $_GET['user_type'] ?? 'all';
$filter_user_id = $_GET['user_id'] ?? null;
$department = $_GET['department'] ?? null;

// Build user condition
$user_condition = "";
if ($user_type === 'individual' && $filter_user_id) {
    $user_condition = "AND user_id = " . (int)$filter_user_id;
} elseif ($user_type === 'department' && $department) {
    $user_condition = "AND department = '" . $conn->real_escape_string($department) . "'";
}

// Build date conditions
$date_condition = "";
switch ($date_range) {
    case 'today':
        $date_condition = "DATE(date_added) = CURDATE()";
        break;
    case 'week':
        $date_condition = "YEARWEEK(date_added) = YEARWEEK(CURDATE())";
        break;
    case 'month':
        $date_condition = "YEAR(date_added) = YEAR(CURDATE()) AND MONTH(date_added) = MONTH(CURDATE())";
        break;
    case 'quarter':
        $date_condition = "YEAR(date_added) = YEAR(CURDATE()) AND QUARTER(date_added) = QUARTER(CURDATE())";
        break;
    case 'year':
        $date_condition = "YEAR(date_added) = YEAR(CURDATE())";
        break;
    case 'custom':
        if ($start_date && $end_date) {
            $date_condition = "DATE(date_added) BETWEEN '$start_date' AND '$end_date'";
        }
        break;
}

// Build category condition
$category_condition = $category !== 'all' ? "AND category = '" . $conn->real_escape_string($category) . "'" : "";

function getMonthlyData($type, $months, $date_condition, $category_condition, $user_condition) {
    global $conn;
    
    $table = $type === 'income' ? 'income' : 'expenses';
    $sql = "SELECT DATE_FORMAT(date_added, '%Y-%m') as month, SUM(amount) as total 
            FROM $table 
            WHERE $date_condition $category_condition $user_condition
            GROUP BY DATE_FORMAT(date_added, '%Y-%m') 
            ORDER BY month DESC 
            LIMIT ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $months);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $monthly_data = array();
    while ($row = $result->fetch_assoc()) {
        $month = date('M Y', strtotime($row['month'] . '-01'));
        $monthly_data[$month] = (float)$row['total'];
    }
    
    return array_reverse($monthly_data);
}

function getCategoryData($date_condition, $category_condition, $user_condition) {
    global $conn;
    
    $sql = "SELECT category, SUM(amount) as total 
            FROM expenses 
            WHERE $date_condition $category_condition $user_condition
            GROUP BY category 
            ORDER BY total DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $category_data = array();
    while ($row = $result->fetch_assoc()) {
        $category_data[$row['category']] = (float)$row['total'];
    }
    
    return $category_data;
}

// Get chart data based on filters
$monthly_expenses = getMonthlyData('expenses', $chart_range, $date_condition, $category_condition, $user_condition);
$monthly_income = getMonthlyData('income', $chart_range, $date_condition, $category_condition, $user_condition);
$expense_by_category = getCategoryData($date_condition, $category_condition, $user_condition);

// Prepare data for charts
$chart_labels = array_keys($monthly_expenses);
$expense_data = array_values($monthly_expenses);
$income_data = array_values($monthly_income);

// Prepare category data
$category_labels = array_keys($expense_by_category);
$category_data = array_values($expense_by_category);
$category_colors = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
    '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
];

// Get summary metrics
$total_income = array_sum($income_data);
$total_expenses = array_sum($expense_data);
$net_savings = $total_income - $total_expenses;
$savings_rate = $total_income > 0 ? ($net_savings / $total_income) * 100 : 0;

// Return data as JSON
$chart_data = [
    'monthly' => [
        'labels' => $chart_labels,
        'expenses' => $expense_data,
        'income' => $income_data,
        'type' => $chart_type
    ],
    'category' => [
        'labels' => $category_labels,
        'data' => $category_data,
        'colors' => $category_colors,
        'type' => $_GET['category_chart_type'] ?? 'doughnut'
    ],
    'metrics' => [
        'total_income' => $total_income,
        'total_expenses' => $total_expenses,
        'net_savings' => $net_savings,
        'savings_rate' => $savings_rate
    ],
    'filters' => [
        'user_type' => $user_type,
        'user_id' => $filter_user_id,
        'department' => $department,
        'date_range' => $date_range,
        'category' => $category
    ]
];

header('Content-Type: application/json');
echo json_encode($chart_data);
?>