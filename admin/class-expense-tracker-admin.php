<?php
if (!defined('ABSPATH')) {
    exit;
}

class Expense_Tracker_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/expense-tracker-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('expense-tracker-chart-styles', plugin_dir_url(__FILE__) . '../assets/css/chart-styles.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
        wp_enqueue_script($this->plugin_name . '-realtime', plugin_dir_url(__FILE__) . '../assets/js/realtime-updates.js', array('jquery'), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-filters', plugin_dir_url(__FILE__) . '../assets/js/dashboard-filters.js', array('jquery'), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-charts', plugin_dir_url(__FILE__) . '../assets/js/chart-init.js', array('chart-js'), $this->version, true);
        
        wp_localize_script($this->plugin_name . '-realtime', 'expenseTrackerAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('expense_tracker_nonce')
        ));
    }

    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'expense_tracker_summary',
            'Expense Tracker Summary',
            array($this, 'render_dashboard_widget')
        );
    }

    public function render_dashboard_widget() {
        $expense_tracker = new Expense_Tracker();
        $user_id = get_current_user_id();
        
        $total_income = $expense_tracker->get_total_income($user_id);
        $total_expenses = $expense_tracker->get_total_expenses($user_id);
        $balance = $total_income - $total_expenses;
        
        include plugin_dir_path(__FILE__) . 'partials/dashboard-widget.php';
    }

    public function save_expense() {
        check_ajax_referer('expense_tracker_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $expense = array(
            'title' => sanitize_text_field($_POST['title']),
            'amount' => floatval($_POST['amount']),
            'category' => sanitize_text_field($_POST['category']),
            'date_added' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );
        
        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'expenses',
            $expense,
            array('%s', '%f', '%s', '%s', '%d')
        );
        
        if ($result) {
            wp_send_json_success('Expense saved successfully');
        } else {
            wp_send_json_error('Failed to save expense');
        }
    }

    public function save_income() {
        check_ajax_referer('expense_tracker_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $income = array(
            'title' => sanitize_text_field($_POST['title']),
            'amount' => floatval($_POST['amount']),
            'source' => sanitize_text_field($_POST['source']),
            'date_added' => current_time('mysql'),
            'user_id' => get_current_user_id()
        );
        
        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'income',
            $income,
            array('%s', '%f', '%s', '%s', '%d')
        );
        
        if ($result) {
            wp_send_json_success('Income saved successfully');
        } else {
            wp_send_json_error('Failed to save income');
        }
    }

    public function get_metrics() {
        check_ajax_referer('expense_tracker_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $expense_tracker = new Expense_Tracker();
        $user_id = get_current_user_id();
        
        $data = array(
            'total_income' => $expense_tracker->get_total_income($user_id),
            'total_expenses' => $expense_tracker->get_total_expenses($user_id),
            'monthly_data' => $expense_tracker->get_monthly_data('all', 12, $user_id),
            'category_data' => $expense_tracker->get_category_data($user_id),
            'recent_transactions' => array(
                'expenses' => $expense_tracker->get_recent_transactions('expenses', 5, $user_id),
                'income' => $expense_tracker->get_recent_transactions('income', 5, $user_id)
            )
        );
        
        wp_send_json_success($data);
    }

    public function export_data() {
        check_ajax_referer('expense_tracker_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $format = sanitize_text_field($_GET['format']);
        $type = sanitize_text_field($_GET['type']);
        $date_range = sanitize_text_field($_GET['date_range']);
        $start_date = sanitize_text_field($_GET['start_date']);
        $end_date = sanitize_text_field($_GET['end_date']);
        
        $expense_tracker = new Expense_Tracker();
        $result = $expense_tracker->export_data($format, $type, $date_range, $start_date, $end_date);
        
        if ($result) {
            wp_send_json_success(array('download_url' => $result));
        } else {
            wp_send_json_error('Export failed');
        }
    }

    public function render_settings_page() {
        include plugin_dir_path(__FILE__) . 'partials/settings-page.php';
    }
}