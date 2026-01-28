<?php

class SM_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function add_menu_pages() {
        add_menu_page(
            'إدارة المدرسة',
            'إدارة المدرسة',
            'read', // Allow all roles to see top level
            'sm-dashboard',
            array($this, 'display_dashboard'),
            'dashicons-welcome-learn-more',
            6
        );

        add_submenu_page(
            'sm-dashboard',
            'لوحة التحكم',
            'لوحة التحكم',
            'read',
            'sm-dashboard',
            array($this, 'display_dashboard')
        );

        add_submenu_page(
            'sm-dashboard',
            'تسجيل مخالفة',
            'تسجيل مخالفة',
            'تسجيل_مخالفة',
            'sm-record-violation',
            array($this, 'display_record_violation')
        );

        add_submenu_page(
            'sm-dashboard',
            'إدارة الطلاب',
            'إدارة الطلاب',
            'إدارة_الطلاب',
            'sm-students',
            array($this, 'display_students')
        );

        add_submenu_page(
            'sm-dashboard',
            'المعلمون',
            'المعلمون',
            'إدارة_المعلمين',
            'sm-teachers',
            array($this, 'display_teachers_page')
        );

        add_submenu_page(
            'sm-dashboard',
            'التقارير والإحصائيات',
            'التقارير والإحصائيات',
            'طباعة_التقارير',
            'sm-reports',
            array($this, 'display_reports')
        );

        add_submenu_page(
            'sm-dashboard',
            'إعدادات النظام',
            'إعدادات النظام',
            'إدارة_النظام',
            'sm-settings',
            array($this, 'display_settings')
        );
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, SM_PLUGIN_URL . 'assets/css/sm-admin.css', array(), $this->version, 'all');
    }

    public function display_dashboard() {
        $stats = SM_DB::get_statistics();
        include SM_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    public function display_record_violation() {
        if (isset($_POST['sm_save_record'])) {
            check_admin_referer('sm_record_action', 'sm_nonce');
            $record_id = SM_DB::add_record($_POST);
            if ($record_id) {
                SM_Notifications::send_violation_alert($record_id);
                echo '<div class="updated"><p>تم تسجيل المخالفة بنجاح.</p></div>';
            }
        }
        $students = SM_DB::get_students();
        include SM_PLUGIN_DIR . 'templates/system-form.php';
    }

    public function display_settings() {
        $student_filters = array();
        $records = array();
        $students = SM_DB::get_students();
        include SM_PLUGIN_DIR . 'templates/public-admin-panel.php';
    }

    public function display_teachers_page() {
        include SM_PLUGIN_DIR . 'templates/admin-teachers.php';
    }

    public function display_records() {
        if (isset($_POST['sm_update_record'])) {
            check_admin_referer('sm_record_action', 'sm_nonce');
            if (current_user_can('إدارة_المخالفات')) {
                SM_DB::update_record(intval($_POST['record_id']), $_POST);
                echo '<div class="updated"><p>تم تحديث السجل بنجاح.</p></div>';
            }
        }

        $filters = array();
        if (isset($_GET['student_filter'])) $filters['student_id'] = intval($_GET['student_filter']);
        if (isset($_GET['start_date'])) $filters['start_date'] = sanitize_text_field($_GET['start_date']);
        if (isset($_GET['end_date'])) $filters['end_date'] = sanitize_text_field($_GET['end_date']);
        if (isset($_GET['type_filter'])) $filters['type'] = sanitize_text_field($_GET['type_filter']);

        // Teacher filter
        if (!current_user_can('إدارة_المستخدمين') && current_user_can('تسجيل_مخالفة')) {
            $filters['teacher_id'] = get_current_user_id();
        }

        $records = SM_DB::get_records($filters);
        include SM_PLUGIN_DIR . 'templates/public-dashboard-stats.php';
    }

    public function display_students() {
        if (isset($_POST['add_student']) && check_admin_referer('sm_add_student', 'sm_nonce')) {
            $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
            SM_DB::add_student($_POST['name'], $_POST['class'], $_POST['email'], $_POST['code'], $parent_user_id);
            echo '<div class="updated"><p>تم إضافة الطالب بنجاح.</p></div>';
        }
        if (isset($_POST['delete_student']) && check_admin_referer('sm_add_student', 'sm_nonce')) {
            SM_DB::delete_student($_POST['delete_student_id']);
            echo '<div class="updated"><p>تم حذف الطالب بنجاح.</p></div>';
        }
        $students = SM_DB::get_students();
        include SM_PLUGIN_DIR . 'templates/admin-students.php';
    }

    public function display_reports() {
        $stats = SM_DB::get_statistics();
        $records = SM_DB::get_records();
        include SM_PLUGIN_DIR . 'templates/admin-reports.php';
    }
}
