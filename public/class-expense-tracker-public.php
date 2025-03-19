<?php
if (!defined('ABSPATH')) {
    exit;
}

class Expense_Tracker_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        // Enqueue Tailwind CSS
        wp_enqueue_style('tailwind-css', 'https://cdn.tailwindcss.com', array(), null);
        
        // Enqueue Font Awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0');
        
        // Plugin specific styles
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/expense-tracker-public.css', array(), $this->version, 'all');
        wp_enqueue_style('expense-tracker-chart-styles', plugin_dir_url(__FILE__) . '../assets/css/chart-styles.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        // Chart.js
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
        
        // Plugin specific scripts
        wp_enqueue_script($this->plugin_name . '-realtime', plugin_dir_url(__FILE__) . '../assets/js/realtime-updates.js', array('jquery'), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-filters', plugin_dir_url(__FILE__) . '../assets/js/dashboard-filters.js', array('jquery'), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-charts', plugin_dir_url(__FILE__) . '../assets/js/chart-init.js', array('chart-js'), $this->version, true);
        
        wp_localize_script($this->plugin_name . '-realtime', 'expenseTrackerAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('expense_tracker_nonce'),
            'is_logged_in' => is_user_logged_in()
        ));
    }

    public function render_dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p class="text-red-500">Please log in to view the expense tracker dashboard.</p>';
        }

        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/dashboard-shortcode.php';
        return ob_get_clean();
    }

    public function render_summary_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '';
        }

        $expense_tracker = new Expense_Tracker();
        $user_id = get_current_user_id();
        
        $atts = shortcode_atts(array(
            'show_balance' => 'true',
            'show_income' => 'true',
            'show_expenses' => 'true',
            'period' => 'all'
        ), $atts);

        $data = array(
            'total_income' => $atts['show_income'] === 'true' ? $expense_tracker->get_total_income($user_id) : null,
            'total_expenses' => $atts['show_expenses'] === 'true' ? $expense_tracker->get_total_expenses($user_id) : null
        );

        if ($atts['show_balance'] === 'true') {
            $data['balance'] = $data['total_income'] - $data['total_expenses'];
        }

        ob_start();
        include plugin_dir_path(__FILE__) . 'partials/summary-shortcode.php';
        return ob_get_clean();
    }

    public function handle_form_submission() {
        if (!isset($_POST['expense_tracker_nonce']) || 
            !wp_verify_nonce($_POST['expense_tracker_nonce'], 'expense_tracker_form')) {
            wp_die('Security check failed');
        }

        if (!is_user_logged_in()) {
            wp_die('Unauthorized');
        }

        $type = sanitize_text_field($_POST['type']);
        $title = sanitize_text_field($_POST['title']);
        $amount = floatval($_POST['amount']);
        $category = sanitize_text_field($_POST['category']);
        $date = sanitize_text_field($_POST['date']);
        
        global $wpdb;
        $table = $type === 'income' ? $wpdb->prefix . 'income' : $wpdb->prefix . 'expenses';
        
        $data = array(
            'user_id' => get_current_user_id(),
            'title' => $title,
            'amount' => $amount,
            'date_added' => $date ?: current_time('mysql')
        );
        
        if ($type === 'income') {
            $data['source'] = $category;
        } else {
            $data['category'] = $category;
        }
        
        $result = $wpdb->insert($table, $data);
        
        if ($result) {
            wp_send_json_success('Entry saved successfully');
        } else {
            wp_send_json_error('Failed to save entry');
        }
    }

    public function register_shortcodes() {
        add_shortcode('expense_tracker', array($this, 'render_dashboard_shortcode'));
        add_shortcode('expense_summary', array($this, 'render_summary_shortcode'));
    }

    public function init() {
        $this->register_shortcodes();
        add_action('wp_ajax_save_expense_entry', array($this, 'handle_form_submission'));
    }
}