<?php
if (!defined('ABSPATH')) {
    exit;
}

class Expense_Tracker {
    private $version;
    private $plugin_name;

    public function __construct() {
        $this->version = '1.0.0';
        $this->plugin_name = 'expense-tracker';
    }

    public function init() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        // Load required files
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/expense-functions.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-expense-tracker-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-expense-tracker-public.php';
    }

    private function set_locale() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'expense-tracker',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    private function define_admin_hooks() {
        $plugin_admin = new Expense_Tracker_Admin($this->get_plugin_name(), $this->get_version());

        // Admin styles and scripts
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));

        // Ajax handlers
        add_action('wp_ajax_save_expense', array($plugin_admin, 'save_expense'));
        add_action('wp_ajax_save_income', array($plugin_admin, 'save_income'));
        add_action('wp_ajax_get_metrics', array($plugin_admin, 'get_metrics'));
        add_action('wp_ajax_export_data', array($plugin_admin, 'export_data'));

        // Dashboard widgets
        add_action('wp_dashboard_setup', array($plugin_admin, 'add_dashboard_widgets'));
    }

    private function define_public_hooks() {
        $plugin_public = new Expense_Tracker_Public($this->get_plugin_name(), $this->get_version());

        // Public styles and scripts
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));

        // Shortcodes
        add_shortcode('expense_tracker_dashboard', array($plugin_public, 'render_dashboard_shortcode'));
        add_shortcode('expense_summary', array($plugin_public, 'render_summary_shortcode'));
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    // Database operations
    public function get_total_income($user_id = null) {
        global $wpdb;
        $user_id = $user_id ?: get_current_user_id();
        
        $sql = $wpdb->prepare(
            "SELECT SUM(amount) as total FROM {$wpdb->prefix}income WHERE user_id = %d",
            $user_id
        );
        
        return (float) $wpdb->get_var($sql);
    }

    public function get_total_expenses($user_id = null) {
        global $wpdb;
        $user_id = $user_id ?: get_current_user_id();
        
        $sql = $wpdb->prepare(
            "SELECT SUM(amount) as total FROM {$wpdb->prefix}expenses WHERE user_id = %d",
            $user_id
        );
        
        return (float) $wpdb->get_var($sql);
    }

    public function get_recent_transactions($type, $limit = 5, $user_id = null) {
        global $wpdb;
        $user_id = $user_id ?: get_current_user_id();
        $table = ($type === 'income') ? 'income' : 'expenses';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}{$table} 
            WHERE user_id = %d 
            ORDER BY date_added DESC 
            LIMIT %d",
            $user_id,
            $limit
        );
        
        return $wpdb->get_results($sql);
    }

    public function get_monthly_data($type, $months = 12, $user_id = null) {
        global $wpdb;
        $user_id = $user_id ?: get_current_user_id();
        $table = ($type === 'income') ? 'income' : 'expenses';
        
        $sql = $wpdb->prepare(
            "SELECT DATE_FORMAT(date_added, '%Y-%m') as month, 
            SUM(amount) as total 
            FROM {$wpdb->prefix}{$table} 
            WHERE user_id = %d 
            GROUP BY month 
            ORDER BY month DESC 
            LIMIT %d",
            $user_id,
            $months
        );
        
        return $wpdb->get_results($sql);
    }

    public function get_category_data($user_id = null) {
        global $wpdb;
        $user_id = $user_id ?: get_current_user_id();
        
        $sql = $wpdb->prepare(
            "SELECT category, SUM(amount) as total 
            FROM {$wpdb->prefix}expenses 
            WHERE user_id = %d 
            GROUP BY category 
            ORDER BY total DESC",
            $user_id
        );
        
        return $wpdb->get_results($sql);
    }

    // Export functionality
    public function export_data($format, $type, $date_range, $start_date = null, $end_date = null) {
        $data = $this->get_export_data($type, $date_range, $start_date, $end_date);
        
        switch ($format) {
            case 'csv':
                return $this->export_to_csv($data);
            case 'excel':
                return $this->export_to_excel($data);
            case 'pdf':
                return $this->export_to_pdf($data);
            default:
                return false;
        }
    }

    private function get_export_data($type, $date_range, $start_date, $end_date) {
        global $wpdb;
        $user_id = get_current_user_id();
        
        // Build date condition
        $date_condition = $this->build_date_condition($date_range, $start_date, $end_date);
        
        // Get data based on type
        if ($type === 'all' || $type === 'income') {
            $income_sql = "SELECT 'Income' as type, title, amount, source as category, 
                          date_added, user_id 
                          FROM {$wpdb->prefix}income 
                          WHERE user_id = %d $date_condition";
            $income_data = $wpdb->get_results($wpdb->prepare($income_sql, $user_id));
        }
        
        if ($type === 'all' || $type === 'expenses') {
            $expenses_sql = "SELECT 'Expense' as type, title, amount, category, 
                           date_added, user_id 
                           FROM {$wpdb->prefix}expenses 
                           WHERE user_id = %d $date_condition";
            $expenses_data = $wpdb->get_results($wpdb->prepare($expenses_sql, $user_id));
        }
        
        return array_merge(
            $type === 'all' || $type === 'income' ? $income_data : array(),
            $type === 'all' || $type === 'expenses' ? $expenses_data : array()
        );
    }

    private function build_date_condition($date_range, $start_date, $end_date) {
        switch ($date_range) {
            case 'today':
                return "AND DATE(date_added) = CURDATE()";
            case 'week':
                return "AND YEARWEEK(date_added) = YEARWEEK(CURDATE())";
            case 'month':
                return "AND YEAR(date_added) = YEAR(CURDATE()) 
                       AND MONTH(date_added) = MONTH(CURDATE())";
            case 'year':
                return "AND YEAR(date_added) = YEAR(CURDATE())";
            case 'custom':
                if ($start_date && $end_date) {
                    return "AND date_added BETWEEN '$start_date' AND '$end_date'";
                }
            default:
                return "";
        }
    }
}