<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once 'functions.php';

// Prevent output buffering
if (ob_get_level()) ob_end_clean();

while (true) {
    // Get latest metrics
    $metrics = [
        'summary' => [
            'total_income' => getTotalIncome(),
            'total_expense' => getTotalExpense(),
            'balance' => getBalance()
        ],
        'charts' => [
            'monthly' => [
                'income' => getMonthlyIncome(),
                'expenses' => getMonthlyExpenses()
            ],
            'category' => getCategoryData()
        ],
        'transactions' => [
            'expenses' => getExpenses(5),
            'income' => getIncomes(5)
        ]
    ];

    // Send the data
    echo "data: " . json_encode($metrics) . "\n\n";

    // Flush the output buffer
    ob_flush();
    flush();

    // Wait for 5 seconds before next update
    sleep(5);
}

function getMonthlyIncome() {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT DATE_FORMAT(date_added, '%Y-%m') as month, SUM(amount) as total 
            FROM income 
            WHERE YEAR(date_added) = YEAR(CURDATE()) 
            GROUP BY DATE_FORMAT(date_added, '%Y-%m') 
            ORDER BY month DESC 
            LIMIT 12";
            
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[$row['month']] = (float)$row['total'];
    }
    
    return array_values($data);
}

function getMonthlyExpenses() {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT DATE_FORMAT(date_added, '%Y-%m') as month, SUM(amount) as total 
            FROM expenses 
            WHERE YEAR(date_added) = YEAR(CURDATE()) 
            GROUP BY DATE_FORMAT(date_added, '%Y-%m') 
            ORDER BY month DESC 
            LIMIT 12";
            
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[$row['month']] = (float)$row['total'];
    }
    
    return array_values($data);
}

function getCategoryData() {
    global $conn;
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT category, SUM(amount) as total 
            FROM expenses 
            WHERE YEAR(date_added) = YEAR(CURDATE()) 
            GROUP BY category 
            ORDER BY total DESC";
            
    $result = $conn->query($sql);
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[$row['category']] = (float)$row['total'];
    }
    
    return [
        'labels' => array_keys($data),
        'data' => array_values($data)
    ];
}
?>