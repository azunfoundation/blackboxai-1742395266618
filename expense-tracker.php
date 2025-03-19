<?php
/*
Plugin Name: WordPress Expense Tracker
Plugin URI: 
Description: A comprehensive expense tracking system with real-time updates and data export
Version: 1.0
Author: BLACKBOXAI
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation
function expense_tracker_activate() {
    global $wpdb;
    
    // Create expenses table
    $expenses_table = $wpdb->prefix . 'expenses';
    $sql = "CREATE TABLE IF NOT EXISTS $expenses_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT,
        title VARCHAR(255),
        amount DECIMAL(10,2),
        category VARCHAR(100),
        date_added DATETIME,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID)
    )";
    $wpdb->query($sql);
    
    // Create income table
    $income_table = $wpdb->prefix . 'income';
    $sql = "CREATE TABLE IF NOT EXISTS $income_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT,
        title VARCHAR(255),
        amount DECIMAL(10,2),
        source VARCHAR(100),
        date_added DATETIME,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID)
    )";
    $wpdb->query($sql);
    
    // Create plugin directory structure
    $upload_dir = wp_upload_dir();
    $plugin_dir = $upload_dir['basedir'] . '/expense-tracker';
    
    if (!file_exists($plugin_dir)) {
        mkdir($plugin_dir, 0755, true);
        mkdir($plugin_dir . '/exports', 0755, true);
    }
}
register_activation_hook(__FILE__, 'expense_tracker_activate');

// Plugin deactivation
function expense_tracker_deactivate() {
    // Cleanup temporary files
    $upload_dir = wp_upload_dir();
    $export_dir = $upload_dir['basedir'] . '/expense-tracker/exports';
    
    if (file_exists($export_dir)) {
        array_map('unlink', glob("$export_dir/*.*"));
    }
}
register_deactivation_hook(__FILE__, 'expense_tracker_deactivate');

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/expense-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-expense-tracker.php';

// Initialize plugin
function init_expense_tracker() {
    $expense_tracker = new Expense_Tracker();
    $expense_tracker->init();
}
add_action('plugins_loaded', 'init_expense_tracker');

// Register assets
function register_expense_tracker_assets() {
    // Styles
    wp_register_style(
        'expense-tracker-styles',
        plugins_url('assets/css/style.css', __FILE__),
        array(),
        '1.0'
    );
    
    wp_register_style(
        'expense-tracker-chart-styles',
        plugins_url('assets/css/chart-styles.css', __FILE__),
        array(),
        '1.0'
    );
    
    // Scripts
    wp_register_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        array(),
        null,
        true
    );
    
    wp_register_script(
        'expense-tracker-realtime',
        plugins_url('assets/js/realtime-updates.js', __FILE__),
        array('jquery'),
        '1.0',
        true
    );
    
    wp_register_script(
        'expense-tracker-filters',
        plugins_url('assets/js/dashboard-filters.js', __FILE__),
        array('jquery'),
        '1.0',
        true
    );
    
    wp_register_script(
        'expense-tracker-charts',
        plugins_url('assets/js/chart-init.js', __FILE__),
        array('chart-js'),
        '1.0',
        true
    );
}
add_action('init', 'register_expense_tracker_assets');

// Add menu items
function add_expense_tracker_menu() {
    add_menu_page(
        'Expense Tracker',
        'Expense Tracker',
        'manage_options',
        'expense-tracker',
        'render_expense_dashboard',
        'dashicons-chart-area',
        6
    );
    
    add_submenu_page(
        'expense-tracker',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'expense-tracker',
        'render_expense_dashboard'
    );
    
    add_submenu_page(
        'expense-tracker',
        'Add Expense',
        'Add Expense',
        'manage_options',
        'add-expense',
        'render_add_expense'
    );
    
    add_submenu_page(
        'expense-tracker',
        'Add Income',
        'Add Income',
        'manage_options',
        'add-income',
        'render_add_income'
    );
    
    add_submenu_page(
        'expense-tracker',
        'Settings',
        'Settings',
        'manage_options',
        'expense-tracker-settings',
        'render_settings'
    );
}
add_action('admin_menu', 'add_expense_tracker_menu');

// Add settings link to plugins page
function add_expense_tracker_settings_link($links) {
    $settings_link = '<a href="admin.php?page=expense-tracker-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'add_expense_tracker_settings_link');