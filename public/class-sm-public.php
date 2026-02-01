<?php

class SM_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function hide_admin_bar_for_non_admins($show) {
        if (!current_user_can('manage_options') || current_user_can('إدارة_النظام')) {
            // System Admins (sm_system_admin) have manage_system/إدارة_النظام
            // User wants admin bar hidden for System Administrator too.
            // Central Control is the only one with 'administrator' role.
            if (!current_user_can('administrator')) {
                return false;
            }
        }
        return $show;
    }

    public function restrict_admin_access() {
        if (is_user_logged_in()) {
            $status = get_user_meta(get_current_user_id(), 'sm_account_status', true);
            if ($status === 'restricted') {
                wp_logout();
                wp_redirect(home_url('/sm-login?login=failed'));
                exit;
            }
        }

        if (is_admin() && !defined('DOING_AJAX') && !current_user_can('manage_options')) {
            wp_redirect(home_url('/sm-admin'));
            exit;
        }
    }

    public function enqueue_styles() {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_style('dashicons');
        wp_enqueue_style('google-font-rubik', 'https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700;800;900&display=swap', array(), null);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true);
        wp_enqueue_script('html5-qrcode', 'https://unpkg.com/html5-qrcode', array(), '2.3.8', true);
        wp_enqueue_style($this->plugin_name, SM_PLUGIN_URL . 'assets/css/sm-public.css', array('dashicons'), $this->version, 'all');

        $appearance = SM_Settings::get_appearance();
        $custom_css = "
            :root {
                --sm-primary-color: {$appearance['primary_color']};
                --sm-secondary-color: {$appearance['secondary_color']};
                --sm-accent-color: {$appearance['accent_color']};
                --sm-dark-color: {$appearance['dark_color']};
                --sm-radius: {$appearance['border_radius']};
            }
            .sm-content-wrapper, .sm-admin-dashboard, .sm-container,
            .sm-content-wrapper *:not(.dashicons), .sm-admin-dashboard *:not(.dashicons), .sm-container *:not(.dashicons) {
                font-family: 'Rubik', sans-serif !important;
            }
            .sm-admin-dashboard { font-size: {$appearance['font_size']}; }
        ";
        wp_add_inline_style($this->plugin_name, $custom_css);
    }

    public function register_shortcodes() {
        add_shortcode('sm_login', array($this, 'shortcode_login'));
        add_shortcode('sm_admin', array($this, 'shortcode_admin_dashboard'));
        add_shortcode('sm_class_attendance', array($this, 'shortcode_class_attendance'));
    }

    public function shortcode_login() {
        if (is_user_logged_in()) {
            wp_redirect(home_url('/sm-admin'));
            exit;
        }
        $output = '';
        if (isset($_GET['login']) && $_GET['login'] == 'failed') {
            $output .= '<div style="color:red; margin-bottom:10px;" dir="rtl">خطأ في اسم المستخدم أو كلمة المرور.</div>';
        }
        $args = array(
            'echo' => false,
            'redirect' => home_url('/sm-admin'), // Standard redirect
            'form_id' => 'sm_login_form',
            'label_username' => 'اسم المستخدم',
            'label_password' => 'كلمة المرور',
            'label_remember' => 'تذكرني',
            'label_log_in' => 'تسجيل الدخول',
            'remember' => true
        );
        return '<div class="sm-container" style="max-width: 450px; margin: 60px auto;" dir="rtl"><h3 style="text-align:center;">تسجيل دخول النظام</h3>' . $output . wp_login_form($args) . '</div>';
    }


    public function shortcode_admin_dashboard() {
        if (!is_user_logged_in()) {
            return $this->shortcode_login();
        }

        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        $active_tab = isset($_GET['sm_tab']) ? sanitize_text_field($_GET['sm_tab']) : 'summary';
        
        // Data Preparation based on tab
        $is_admin = in_array('administrator', $roles) || current_user_can('manage_options');
        $is_sys_admin = in_array('sm_system_admin', $roles);
        $is_principal = in_array('sm_principal', $roles);
        $is_supervisor = in_array('sm_supervisor', $roles);
        $is_coordinator = in_array('sm_coordinator', $roles);
        $is_teacher = in_array('sm_teacher', $roles);
        $is_student = in_array('sm_student', $roles);

        // Security / Capability check for tabs
        if ($active_tab === 'record' && !current_user_can('تسجيل_مخالفة')) $active_tab = 'summary';
        if ($active_tab === 'students' && !current_user_can('إدارة_الطلاب')) $active_tab = 'summary';
        if ($active_tab === 'teachers' && !current_user_can('إدارة_المستخدمين')) $active_tab = 'summary';
        if ($active_tab === 'teacher-reports' && !current_user_can('إدارة_المخالفات')) $active_tab = 'summary';
        if ($active_tab === 'confiscated' && !current_user_can('إدارة_المخالفات')) $active_tab = 'summary';
        if ($active_tab === 'printing' && !current_user_can('طباعة_التقارير')) $active_tab = 'summary';
        if ($active_tab === 'attendance' && !current_user_can('إدارة_الطلاب')) $active_tab = 'summary';
        if ($active_tab === 'global-settings' && !current_user_can('إدارة_النظام')) $active_tab = 'summary';
        if ($active_tab === 'lesson-plans' && !($is_coordinator || $is_teacher)) $active_tab = 'summary';
        if ($active_tab === 'assignments' && !($is_teacher || $is_student)) $active_tab = 'summary';

        // Fetch data based on tab
        switch ($active_tab) {
            case 'summary':
                if ($is_student) {
                    $student = SM_DB::get_student_by_parent($user->ID);
                    $student_id = $student ? $student->id : 0;
                    $stats = SM_DB::get_student_stats($student_id);
                } else {
                    $stats = SM_DB::get_statistics($is_teacher && !$is_admin ? ['teacher_id' => $user->ID] : []);
                }
                break;

            case 'students':
                $args = array();
                if (isset($_GET['student_search'])) $args['search'] = sanitize_text_field($_GET['student_search']);
                if (isset($_GET['class_filter'])) $args['class_name'] = sanitize_text_field($_GET['class_filter']);
                if (isset($_GET['section_filter'])) $args['section'] = sanitize_text_field($_GET['section_filter']);
                if (isset($_GET['teacher_filter']) && !empty($_GET['teacher_filter'])) $args['teacher_id'] = intval($_GET['teacher_filter']);
                if ($is_teacher && !$is_admin) $args['teacher_id'] = $user->ID;
                $students = SM_DB::get_students($args);
                break;

            case 'stats':
                $filters = array();
                if ($is_parent) {
                    $my_stu = SM_DB::get_students_by_parent($user->ID);
                    $filters['student_id'] = isset($_GET['student_id']) ? intval($_GET['student_id']) : ($my_stu[0]->id ?? 0);
                } else {
                    if (isset($_GET['student_filter'])) $filters['student_id'] = intval($_GET['student_filter']);
                    if ($is_teacher && !$is_admin) $filters['teacher_id'] = $user->ID;

                    if (isset($_GET['class_filter'])) $filters['class_name'] = sanitize_text_field($_GET['class_filter']);
                    if (isset($_GET['section_filter'])) $filters['section'] = sanitize_text_field($_GET['section_filter']);
                    if (isset($_GET['student_search'])) $filters['search'] = sanitize_text_field($_GET['student_search']);
                }
                if (isset($_GET['start_date'])) $filters['start_date'] = sanitize_text_field($_GET['start_date']);
                if (isset($_GET['end_date'])) $filters['end_date'] = sanitize_text_field($_GET['end_date']);
                if (isset($_GET['type_filter'])) $filters['type'] = sanitize_text_field($_GET['type_filter']);

                // If no filters are applied, limit to latest 20 for quick access
                $is_filtering = !empty($_GET['student_search']) || !empty($_GET['class_filter']) || !empty($_GET['section_filter']) || !empty($_GET['start_date']) || !empty($_GET['end_date']) || !empty($_GET['type_filter']);
                if (!$is_filtering && !$is_parent) {
                    $filters['limit'] = 20;
                }

                $records = SM_DB::get_records($filters);
                break;

            case 'reports':
                $stats = SM_DB::get_statistics();
                $records = SM_DB::get_records();
                break;

            case 'teacher-reports':
                $records = SM_DB::get_records(array('status' => 'pending'));
                break;

            case 'confiscated':
                $records = SM_DB::get_confiscated_items();
                break;

            case 'attendance':
                $attendance_date = isset($_GET['attendance_date']) ? sanitize_text_field($_GET['attendance_date']) : current_time('Y-m-d');
                $attendance_summary = SM_DB::get_attendance_summary($attendance_date);
                break;
        }

        ob_start();
        include SM_PLUGIN_DIR . 'templates/public-admin-panel.php';
        return ob_get_clean();
    }

    public function login_failed($username) {
        $referrer = wp_get_referer();
        if ($referrer && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
            wp_redirect(add_query_arg('login', 'failed', $referrer));
            exit;
        }
    }

    public function handle_print() {
        $user = wp_get_current_user();
        $student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

        if (in_array('sm_parent', (array) $user->roles)) {
            $my_students = SM_DB::get_students_by_parent($user->ID);
            $is_mine = false;
            foreach ($my_students as $ms) {
                if ($ms->id == $student_id) $is_mine = true;
            }
            if (!$is_mine) wp_die('Unauthorized');
        } elseif (!current_user_can('طباعة_التقارير')) {
            wp_die('Unauthorized');
        }

        $type = sanitize_text_field($_GET['print_type']);
        $student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

        if ($type === 'id_card') {
            if ($student_id) {
                $students = array(SM_DB::get_student_by_id($student_id));
            } else {
                $filters = array();
                if (!empty($_GET['class_name'])) {
                    $filters['class_name'] = sanitize_text_field($_GET['class_name']);
                }
                $students = SM_DB::get_students($filters);
            }
            include SM_PLUGIN_DIR . 'templates/print-id-cards.php';
        } elseif ($type === 'disciplinary_report') {
            if (!$student_id) wp_die('Student ID missing');
            $student = SM_DB::get_student_by_id($student_id);
            $records = SM_DB::get_records(array('student_id' => $student_id));
            $stats = SM_DB::get_student_stats($student_id);
            include SM_PLUGIN_DIR . 'templates/print-student-report.php';
        } elseif ($type === 'single_violation') {
            $record_id = isset($_GET['record_id']) ? intval($_GET['record_id']) : 0;
            if (!$record_id) wp_die('Record ID missing');
            $record = SM_DB::get_record_by_id($record_id);
            if (!$record) wp_die('Record not found');
            
            // Security check for parents
            if (in_array('sm_parent', (array) $user->roles)) {
                $student = SM_DB::get_student_by_parent($user->ID);
                if (!$student || $record->student_id != $student->id) wp_die('Unauthorized');
            }

            include SM_PLUGIN_DIR . 'templates/print-single-violation.php';
        } elseif ($type === 'general_log') {
            $filters = array(
                'start_date' => $_GET['start_date'] ?? '',
                'end_date' => $_GET['end_date'] ?? ''
            );
            $records = SM_DB::get_records($filters);
            include SM_PLUGIN_DIR . 'templates/print-general-log.php';
        } elseif ($type === 'attendance_sheet') {
            $date = sanitize_text_field($_GET['date']);
            $scope = sanitize_text_field($_GET['scope']); // all, grade, section
            $grade = sanitize_text_field($_GET['grade'] ?? '');
            $section = sanitize_text_field($_GET['section'] ?? '');

            global $wpdb;
            $query = "SELECT s.id, s.name, s.student_code, s.class_name, s.section, a.status
                      FROM {$wpdb->prefix}sm_students s
                      LEFT JOIN {$wpdb->prefix}sm_attendance a ON s.id = a.student_id AND a.date = %s
                      WHERE 1=1";
            $params = array($date);

            if ($scope === 'grade' && $grade) {
                $query .= " AND s.class_name = %s";
                $params[] = $grade;
            } elseif ($scope === 'section' && $grade && $section) {
                $query .= " AND s.class_name = %s AND s.section = %s";
                $params[] = $grade;
                $params[] = $section;
            }

            $query .= " ORDER BY s.class_name ASC, s.section ASC, s.name ASC";
            $all_students = $wpdb->get_results($wpdb->prepare($query, $params));

            $grouped_data = array();
            foreach ($all_students as $s) {
                $key = $s->class_name . '|' . $s->section;
                $grouped_data[$key][] = $s;
            }

            include SM_PLUGIN_DIR . 'templates/print-attendance.php';
        } elseif ($type === 'absence_report') {
            $report_type = sanitize_text_field($_GET['type']); // daily or term
            $date = sanitize_text_field($_GET['date'] ?? current_time('Y-m-d'));

            global $wpdb;
            $data = array();
            $title = '';
            $subtitle = '';

            if ($report_type === 'daily') {
                $title = 'تقرير الغياب اليومي - ' . $date;
                $data = $wpdb->get_results($wpdb->prepare(
                    "SELECT s.id, s.name, s.student_code, s.class_name, s.section,
                     (SELECT COUNT(*) FROM {$wpdb->prefix}sm_attendance WHERE student_id = s.id AND status = 'absent') as total_absences
                     FROM {$wpdb->prefix}sm_students s
                     JOIN {$wpdb->prefix}sm_attendance a ON s.id = a.student_id
                     WHERE a.date = %s AND a.status = 'absent'
                     ORDER BY s.class_name ASC, s.section ASC, s.name ASC",
                    $date
                ));
            } else {
                $academic = SM_Settings::get_academic_structure();
                $current_term = null;
                $today = current_time('Y-m-d');
                foreach ($academic['term_dates'] as $t_key => $t_dates) {
                    if (!empty($t_dates['start']) && !empty($t_dates['end'])) {
                        if ($today >= $t_dates['start'] && $today <= $t_dates['end']) {
                            $current_term = $t_dates;
                            $subtitle = 'الفصل الدراسي: ' . $t_key . ' (من ' . $t_dates['start'] . ' إلى ' . $t_dates['end'] . ')';
                            break;
                        }
                    }
                }

                if ($current_term) {
                    $title = 'أكثر الطلاب غياباً خلال الفصل الدراسي';
                    $data = $wpdb->get_results($wpdb->prepare(
                        "SELECT s.id, s.name, s.student_code, s.class_name, s.section, COUNT(a.id) as absence_count
                         FROM {$wpdb->prefix}sm_students s
                         JOIN {$wpdb->prefix}sm_attendance a ON s.id = a.student_id
                         WHERE a.status = 'absent' AND a.date >= %s AND a.date <= %s
                         GROUP BY s.id
                         HAVING absence_count > 0
                         ORDER BY absence_count DESC, s.name ASC
                         LIMIT 50",
                        $current_term['start'], $current_term['end']
                    ));
                } else {
                    $title = 'لا يوجد فصل دراسي حالي محدد في الإعدادات';
                }
            }

            include SM_PLUGIN_DIR . 'templates/print-absence-report.php';
        } elseif ($type === 'student_credentials') {
            $filters = array();
            if (!empty($_GET['class_name'])) {
                $filters['class_name'] = sanitize_text_field($_GET['class_name']);
            }
            $students = SM_DB::get_students($filters);
            include SM_PLUGIN_DIR . 'templates/print-student-credentials.php';
        } elseif ($type === 'student_credentials_card') {
            include SM_PLUGIN_DIR . 'templates/print-student-credentials-card.php';
        }
        exit;
    }

    public function ajax_get_student() {
        if (!is_user_logged_in() || !current_user_can('تسجيل_مخالفة')) wp_send_json_error('Unauthorized');
        $code = sanitize_text_field($_POST['code']);
        $student = SM_DB::get_student_by_code($code);
        if ($student) {
            wp_send_json_success($student);
        } else {
            wp_send_json_error('Student not found');
        }
    }

    public function ajax_search_students() {
        if (!is_user_logged_in() || !current_user_can('تسجيل_مخالفة')) wp_send_json_error('Unauthorized');
        $query = sanitize_text_field($_POST['query']);
        if (strlen($query) < 2) wp_send_json_success(array());

        $args = array('search' => $query);
        // Teachers can search all students as per new requirements
        $students = SM_DB::get_students($args);
        wp_send_json_success($students);
    }

    public function ajax_get_student_intelligence() {
        if (!is_user_logged_in() || !current_user_can('تسجيل_مخالفة')) wp_send_json_error('Unauthorized');
        $student_id = intval($_POST['student_id']);
        if (!$student_id) wp_send_json_error('Invalid ID');

        $stats = SM_DB::get_student_stats($student_id);
        $records = SM_DB::get_records(array('student_id' => $student_id));
        $latest = array_slice($records, 0, 3); // Get 3 latest records
        $student = SM_DB::get_student_by_id($student_id);

        wp_send_json_success(array(
            'stats' => $stats,
            'recent' => $latest,
            'labels' => SM_Settings::get_violation_types(),
            'photo_url' => $student ? $student->photo_url : ''
        ));
    }

    public function ajax_refresh_dashboard() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        
        $user_id = get_current_user_id();
        $stats = SM_DB::get_statistics();
        $records = SM_DB::get_records();
        $logs = SM_Logger::get_logs(50);
        
        global $wpdb;
        $unread_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}sm_messages WHERE receiver_id = %d AND is_read = 0", $user_id));

        wp_send_json_success(array(
            'stats' => $stats,
            'records' => $records,
            'logs' => $logs,
            'unread_messages' => intval($unread_count),
            'violation_labels' => SM_Settings::get_violation_types(),
            'severity_labels' => SM_Settings::get_severities()
        ));
    }

    public function ajax_save_record() {
        if (!is_user_logged_in() || !current_user_can('تسجيل_مخالفة')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_record_action')) wp_send_json_error('Security check failed');

        $student_ids = array_filter(array_map('intval', explode(',', $_POST['student_ids'])));
        $last_record_id = 0;
        $count = 0;
        
        foreach ($student_ids as $sid) {
            $data = $_POST;
            $data['student_id'] = $sid;
            $rid = SM_DB::add_record($data, true); // Skip individual logs
            if ($rid) {
                $last_record_id = $rid;
                $count++;
                SM_Notifications::send_violation_alert($rid);
            }
        }

        if ($count > 0) {
            SM_Logger::log('تسجيل مخالفة جماعية', "تم تسجيل مخالفة لعدد ($count) من الطلاب بنجاح.");
        }

        if ($last_record_id) {
            wp_send_json_success(array(
                'record_id' => $last_record_id,
                'print_url' => admin_url('admin-ajax.php?action=sm_print&print_type=single_violation&record_id=' . $last_record_id)
            ));
        } else {
            wp_send_json_error('Failed to save records');
        }
    }

    public function ajax_update_student_photo() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_photo_nonce'], 'sm_photo_action')) wp_send_json_error('Security check failed');
        
        $user_id = get_current_user_id();
        $student_id = intval($_POST['student_id']);
        
        // Security: Parent can only update their children, Admin can update anyone
        if (!current_user_can('إدارة_الطلاب')) {
            $my_children = SM_DB::get_students_by_parent($user_id);
            $is_mine = false;
            foreach ($my_children as $child) {
                if ($child->id == $student_id) $is_mine = true;
            }
            if (!$is_mine) wp_send_json_error('Permission denied');
        }

        if (empty($_FILES['student_photo'])) wp_send_json_error('No file provided');

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('student_photo', 0);
        if (is_wp_error($attachment_id)) {
            wp_send_json_error($attachment_id->get_error_message());
        }

        $photo_url = wp_get_attachment_url($attachment_id);
        $student_id = intval($_POST['student_id']);
        
        SM_DB::update_student_photo($student_id, $photo_url);
        wp_send_json_success(array('photo_url' => $photo_url));
    }

    public function ajax_send_message() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_message_nonce'], 'sm_message_action')) wp_send_json_error('Security check failed');

        $sender_id = get_current_user_id();
        $receiver_id = intval($_POST['receiver_id']);
        $message = sanitize_textarea_field($_POST['message']);
        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;

        if (SM_DB::send_message($sender_id, $receiver_id, $message, $student_id)) {
            wp_send_json_success('Message sent');
        } else {
            wp_send_json_error('Failed to send message');
        }
    }

    public function ajax_get_conversation() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_message_action')) wp_send_json_error('Security check');

        $my_id = get_current_user_id();
        $other_id = intval($_POST['other_user_id']);

        $messages = SM_DB::get_conversation_messages($my_id, $other_id);
        wp_send_json_success($messages);
    }

    public function ajax_mark_read() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_message_action')) wp_send_json_error('Security check');

        $my_id = get_current_user_id();
        $other_id = intval($_POST['other_user_id']);

        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}sm_messages",
            array('is_read' => 1),
            array('receiver_id' => $my_id, 'sender_id' => $other_id)
        );
        wp_send_json_success();
    }


    public function ajax_update_record_status() {
        if (!is_user_logged_in() || !current_user_can('إدارة_المخالفات')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_record_action')) wp_send_json_error('Security check');

        $record_id = intval($_POST['record_id']);
        $status = sanitize_text_field($_POST['status']);

        if (SM_DB::update_record_status($record_id, $status)) {
            wp_send_json_success('Status updated');
        } else {
            wp_send_json_error('Failed to update status');
        }
    }

    public function ajax_send_group_message() {
        if (!is_user_logged_in() || !current_user_can('إدارة_المستخدمين')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_message_action')) wp_send_json_error('Security check');

        $role = sanitize_text_field($_POST['target_role']);
        $subject = "رسالة جماعية من إدارة المدرسة";
        $message = sanitize_textarea_field($_POST['message']);

        SM_Notifications::send_group_notification($role, $subject, $message);
        wp_send_json_success('Group messages sent');
    }

    public function ajax_add_student() {
        if (!current_user_can('إدارة_الطلاب')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_add_student')) wp_send_json_error('Security check failed');

        $name = sanitize_text_field($_POST['name'] ?? '');
        $class = sanitize_text_field($_POST['class'] ?? '');

        if (empty($name) || empty($class)) {
            wp_send_json_error('الاسم والصف حقول إجبارية');
        }

        $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
        $section = !empty($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
        $email = !empty($_POST['email']) ? sanitize_email($_POST['email']) : '';

        $extra = array(
            'guardian_phone' => sanitize_text_field($_POST['guardian_phone'] ?? ''),
            'nationality' => sanitize_text_field($_POST['nationality'] ?? ''),
            'registration_date' => sanitize_text_field($_POST['registration_date'] ?? '')
        );

        // Check if student exists
        if (SM_DB::student_exists($name, $class, $section)) {
            wp_send_json_error('هذا الطالب مسجل بالفعل في هذا الصف والشعبة.');
        }

        $id = SM_DB::add_student($name, $class, $email, '', $parent_user_id, null, $section, $extra);

        if ($id) {
            wp_send_json_success($id);
        } else {
            wp_send_json_error('فشل في إضافة الطالب. يرجى التحقق من البيانات والمحاولة مرة أخرى.');
        }
    }

    public function ajax_update_student() {
        if (!current_user_can('إدارة_الطلاب')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_add_student')) wp_send_json_error('Security check failed');

        if (SM_DB::update_student(intval($_POST['student_id']), $_POST)) {
            wp_send_json_success('Updated');
        } else {
            wp_send_json_error('Failed to update');
        }
    }

    public function ajax_delete_student() {
        if (!current_user_can('إدارة_الطلاب')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_delete_student')) wp_send_json_error('Security check failed');

        if (SM_DB::delete_student(intval($_POST['student_id']))) {
            wp_send_json_success('Deleted');
        } else {
            wp_send_json_error('Failed to delete');
        }
    }

    public function ajax_add_confiscated() {
        if (!current_user_can('إدارة_المخالفات')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_confiscated_action')) wp_send_json_error('Security check failed');

        $data = $_POST;
        if ($data['item_name'] === 'other' && !empty($data['item_name_other'])) {
            $data['item_name'] = $data['item_name_other'];
        }

        if (SM_DB::add_confiscated_item($data)) {
            wp_send_json_success('Added');
        } else {
            wp_send_json_error('Failed to add');
        }
    }

    public function ajax_update_confiscated() {
        if (!current_user_can('إدارة_المخالفات')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_confiscated_action')) wp_send_json_error('Security check failed');

        if (SM_DB::update_confiscated_item_status(intval($_POST['item_id']), sanitize_text_field($_POST['status']))) {
            wp_send_json_success('Updated');
        } else {
            wp_send_json_error('Failed to update');
        }
    }

    public function ajax_delete_confiscated() {
        if (!current_user_can('إدارة_المخالفات')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_confiscated_action')) wp_send_json_error('Security check failed');

        if (SM_DB::delete_confiscated_item(intval($_POST['item_id']))) {
            wp_send_json_success('Deleted');
        } else {
            wp_send_json_error('Failed to delete');
        }
    }

    public function ajax_delete_record() {
        if (!current_user_can('إدارة_المخالفات')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_record_action')) wp_send_json_error('Security check failed');

        if (SM_DB::delete_record(intval($_POST['record_id']))) {
            wp_send_json_success('Deleted');
        } else {
            wp_send_json_error('Failed to delete');
        }
    }

    public function ajax_get_counts() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        wp_send_json_success(array(
            'pending_reports' => intval(SM_DB::get_pending_reports_count()),
            'expired_items' => intval(SM_DB::get_expired_items_count())
        ));
    }

    public function ajax_add_user() {
        if (!current_user_can('إدارة_المستخدمين')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_user_action')) wp_send_json_error('Security check failed');

        $user_data = array(
            'user_login' => sanitize_user($_POST['user_login']),
            'user_email' => sanitize_email($_POST['user_email']),
            'display_name' => sanitize_text_field($_POST['display_name']),
            'user_pass' => $_POST['user_pass'],
            'role' => sanitize_text_field($_POST['user_role'])
        );
        $user_id = wp_insert_user($user_data);
        if (is_wp_error($user_id)) wp_send_json_error($user_id->get_error_message());
        else wp_send_json_success($user_id);
    }

    public function ajax_update_generic_user() {
        if (!current_user_can('إدارة_المستخدمين')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_user_action')) wp_send_json_error('Security check failed');

        $user_id = intval($_POST['edit_user_id']);
        $user_data = array(
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['user_email']),
            'display_name' => sanitize_text_field($_POST['display_name'])
        );
        if (!empty($_POST['user_pass'])) {
            $user_data['user_pass'] = $_POST['user_pass'];
        }
        $result = wp_update_user($user_data);
        if (is_wp_error($result)) wp_send_json_error($result->get_error_message());
        
        $u = new WP_User($user_id);
        $u->set_role(sanitize_text_field($_POST['user_role']));
        
        wp_send_json_success('Updated');
    }

    public function ajax_add_teacher() {
        if (!current_user_can('إدارة_المستخدمين')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_teacher_action')) wp_send_json_error('Security check failed');

        $pass = $_POST['user_pass'];
        if (empty($pass)) {
            $pass = '';
            for($i=0; $i<10; $i++) $pass .= rand(0,9);
        }

        $user_data = array(
            'user_login' => sanitize_user($_POST['user_login']),
            'user_email' => sanitize_email($_POST['user_email']),
            'display_name' => sanitize_text_field($_POST['display_name']),
            'user_pass' => $pass,
            'role' => sanitize_text_field($_POST['role'] ?: 'sm_teacher')
        );
        $user_id = wp_insert_user($user_data);
        if (is_wp_error($user_id)) wp_send_json_error($user_id->get_error_message());

        update_user_meta($user_id, 'sm_temp_pass', $pass);
        update_user_meta($user_id, 'sm_teacher_id', sanitize_text_field($_POST['teacher_id']));
        update_user_meta($user_id, 'sm_phone', sanitize_text_field($_POST['phone']));

        if (isset($_POST['assigned'])) {
            $assigned = array_map('sanitize_text_field', $_POST['assigned']);
            if ($_POST['role'] === 'sm_teacher') {
                update_user_meta($user_id, 'sm_assigned_sections', $assigned);
            } elseif ($_POST['role'] === 'sm_supervisor') {
                update_user_meta($user_id, 'sm_supervised_classes', $assigned);
            }
        }

        wp_send_json_success($user_id);
    }

    public function ajax_update_profile() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_profile_action')) wp_send_json_error('Security check failed');

        $user_id = get_current_user_id();
        $user_data = array(
            'ID' => $user_id,
            'display_name' => sanitize_text_field($_POST['display_name']),
            'user_email' => sanitize_email($_POST['user_email'])
        );

        if (!empty($_POST['user_pass'])) {
            $user_data['user_pass'] = $_POST['user_pass'];
        }

        $result = wp_update_user($user_data);
        if (is_wp_error($result)) wp_send_json_error($result->get_error_message());
        else wp_send_json_success('Profile updated');
    }

    public function ajax_bulk_delete() {
        if (!current_user_can('إدارة_النظام')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_admin_action')) wp_send_json_error('Security check failed');

        global $wpdb;
        $type = sanitize_text_field($_POST['delete_type']);
        $count = 0;

        switch ($type) {
            case 'students':
                $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_students");
                $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_records");
                SM_Logger::log('مسح كافة الطلاب والسجلات', 'إجراء جماعي');
                break;
            case 'teachers':
                $teachers = get_users(array('role' => 'sm_teacher'));
                foreach ($teachers as $t) {
                    wp_delete_user($t->ID);
                    $count++;
                }
                SM_Logger::log('مسح كافة المعلمين', 'إجراء جماعي');
                break;
            case 'parents':
                $parents = get_users(array('role' => 'sm_parent'));
                foreach ($parents as $p) {
                    wp_delete_user($p->ID);
                    $count++;
                }
                SM_Logger::log('مسح كافة أولياء الأمور', 'إجراء جماعي');
                break;
            case 'records':
                $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_records");
                SM_Logger::log('مسح كافة المخالفات', 'إجراء جماعي');
                break;
        }

        wp_send_json_success('تم مسح البيانات بنجاح');
    }

    public function ajax_get_students_attendance() {
        $class_name = sanitize_text_field($_POST['class_name']);
        $section = sanitize_text_field($_POST['section']);
        $date = sanitize_text_field($_POST['date']);
        $code = sanitize_text_field($_POST['security_code'] ?? '');

        // Security Check: Either Staff or Valid Class Code
        $is_staff = is_user_logged_in() && current_user_can('إدارة_الطلاب');
        $valid_code = (SM_Settings::get_class_security_code($class_name, $section) === $code);

        if (!$is_staff && !$valid_code) {
            wp_send_json_error('Unauthorized: Invalid security code');
        }

        $students = SM_DB::get_students_attendance($class_name, $section, $date);
        wp_send_json_success($students);
    }

    public function shortcode_class_attendance() {
        ob_start();
        include SM_PLUGIN_DIR . 'templates/shortcode-class-attendance.php';
        return ob_get_clean();
    }

    public function ajax_save_attendance() {
        if (!wp_verify_nonce($_POST['nonce'], 'sm_attendance_action')) wp_send_json_error('Security check failed');

        $student_id = intval($_POST['student_id']);
        $status = sanitize_text_field($_POST['status']);
        $date = sanitize_text_field($_POST['date']);
        $code = sanitize_text_field($_POST['security_code'] ?? '');

        // Get student info to check class
        $student = SM_DB::get_student_by_id($student_id);
        if (!$student) wp_send_json_error('Student not found');

        $is_staff = is_user_logged_in() && current_user_can('إدارة_الطلاب');
        $valid_code = (SM_Settings::get_class_security_code($student->class_name, $student->section) === $code);

        if (!$is_staff && !$valid_code) {
            wp_send_json_error('Unauthorized');
        }

        $teacher_id = get_current_user_id(); // 0 for public

        if (SM_DB::save_attendance($student_id, $status, $date, $teacher_id)) {
            wp_send_json_success('Saved');
        } else {
            wp_send_json_error('Failed to save');
        }
    }

    public function ajax_save_attendance_batch() {
        if (!wp_verify_nonce($_POST['nonce'], 'sm_attendance_action')) wp_send_json_error('Security check failed');

        $batch = json_decode(stripslashes($_POST['batch'] ?? '[]'), true);
        if (empty($batch)) wp_send_json_error('Empty batch');

        $first_sid = intval($batch[0]['student_id']);
        $student = SM_DB::get_student_by_id($first_sid);
        if (!$student) wp_send_json_error('Student not found');

        $code = sanitize_text_field($_POST['security_code'] ?? '');
        $is_staff = is_user_logged_in() && current_user_can('إدارة_الطلاب');
        $valid_code = (SM_Settings::get_class_security_code($student->class_name, $student->section) === $code);

        if (!$is_staff && !$valid_code) {
            wp_send_json_error('Unauthorized');
        }

        $date = sanitize_text_field($_POST['date']);
        $teacher_id = get_current_user_id();

        if (!is_array($batch)) wp_send_json_error('Invalid batch data');

        $success_count = 0;
        foreach ($batch as $item) {
            if (SM_DB::save_attendance(intval($item['student_id']), sanitize_text_field($item['status']), $date, $teacher_id)) {
                $success_count++;
            }
        }

        wp_send_json_success($success_count);
    }

    public function ajax_reset_class_code() {
        if (!is_user_logged_in() || !current_user_can('إدارة_الطلاب')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_attendance_action')) wp_send_json_error('Security check failed');

        $grade = sanitize_text_field($_POST['grade']);
        $section = sanitize_text_field($_POST['section']);

        $new_code = SM_Settings::reset_class_security_code($grade, $section);
        wp_send_json_success($new_code);
    }

    public function ajax_toggle_attendance_status() {
        if (!is_user_logged_in() || !current_user_can('إدارة_الطلاب')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_attendance_action')) wp_send_json_error('Security check failed');

        $status = sanitize_text_field($_POST['status']);
        update_option('sm_attendance_manual_status', $status);
        wp_send_json_success();
    }

    public function ajax_rollback_log() {
        if (!current_user_can('إدارة_النظام')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_admin_action')) wp_send_json_error('Security check failed');

        $log_id = intval($_POST['log_id']);
        global $wpdb;
        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sm_logs WHERE id = %d", $log_id));

        if (!$log || strpos($log->details, 'ROLLBACK_DATA:') !== 0) {
            wp_send_json_error('لا يمكن استعادة هذه العملية');
        }

        $json = substr($log->details, strlen('ROLLBACK_DATA:'));
        $data_obj = json_decode($json, true);

        if (!$data_obj || !isset($data_obj['table']) || !isset($data_obj['data'])) {
            wp_send_json_error('بيانات الاستعادة تالفة');
        }

        $table = $data_obj['table'];
        $data = $data_obj['data'];

        // Remove 'id' if we want to insert as new, or keep if we want to restore exact ID (risky if ID taken)
        // For students/records, restoring exact ID is better for relations.

        $table_name = $wpdb->prefix . 'sm_' . $table;

        // Check if ID already exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE id = %d", $data['id']));
        if ($exists) {
            wp_send_json_error('البيانات موجودة بالفعل أو تم استخدام المعرف');
        }

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            $wpdb->delete("{$wpdb->prefix}sm_logs", array('id' => $log_id)); // Remove log after rollback
            SM_Logger::log('استعادة عملية محذوفة', "الجدول: $table، المعرف الأصلي: {$data['id']}");
            wp_send_json_success('تمت الاستعادة بنجاح');
        } else {
            wp_send_json_error('فشلت عملية الاستعادة في قاعدة البيانات');
        }
    }

    public function ajax_initialize_system() {
        if (!current_user_can('إدارة_النظام')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_admin_action')) wp_send_json_error('Security check failed');

        if ($_POST['confirm_code'] !== '1011996') {
            wp_send_json_error('كود التأكيد غير صحيح');
        }

        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/user.php');

        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_students");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_records");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_messages");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_confiscated_items");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}sm_logs");

        $teachers = get_users(array('role' => 'sm_teacher'));
        foreach ($teachers as $t) wp_delete_user($t->ID);

        $parents = get_users(array('role' => 'sm_parent'));
        foreach ($parents as $p) wp_delete_user($p->ID);

        SM_Logger::log('تهيأة النظام بالكامل', 'تم مسح كافة البيانات والجداول');
        wp_send_json_success('تمت تهيأة النظام بالكامل بنجاح');
    }

    public function ajax_update_teacher() {
        if (!current_user_can('إدارة_المستخدمين')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_teacher_action')) wp_send_json_error('Security check failed');

        $user_id = intval($_POST['edit_teacher_id']);
        $user_data = array(
            'ID' => $user_id,
            'user_email' => sanitize_email($_POST['user_email']),
            'display_name' => sanitize_text_field($_POST['display_name'])
        );
        if (!empty($_POST['user_pass'])) {
            $user_data['user_pass'] = $_POST['user_pass'];
            update_user_meta($user_id, 'sm_temp_pass', $_POST['user_pass']);
        }
        $result = wp_update_user($user_data);
        if (is_wp_error($result)) wp_send_json_error($result->get_error_message());

        $u = new WP_User($user_id);
        $role = sanitize_text_field($_POST['role']);
        $u->set_role($role);

        update_user_meta($user_id, 'sm_teacher_id', sanitize_text_field($_POST['teacher_id']));
        update_user_meta($user_id, 'sm_phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'sm_account_status', sanitize_text_field($_POST['account_status']));

        // Clean old assignments
        delete_user_meta($user_id, 'sm_assigned_sections');
        delete_user_meta($user_id, 'sm_supervised_classes');

        if (isset($_POST['assigned'])) {
            $assigned = array_map('sanitize_text_field', $_POST['assigned']);
            if ($role === 'sm_teacher') {
                update_user_meta($user_id, 'sm_assigned_sections', $assigned);
            } elseif ($role === 'sm_supervisor') {
                update_user_meta($user_id, 'sm_supervised_classes', $assigned);
            }
        }

        wp_send_json_success('Updated');
    }

    public function ajax_add_assignment() {
        if (!is_user_logged_in()) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['sm_nonce'], 'sm_assignment_action')) wp_send_json_error('Security check');

        $data = array(
            'sender_id' => get_current_user_id(),
            'receiver_id' => intval($_POST['receiver_id']),
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'file_url' => esc_url_raw($_POST['file_url']),
            'type' => sanitize_text_field($_POST['type'] ?? 'assignment')
        );

        if (SM_DB::add_assignment($data)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed');
        }
    }

    public function ajax_approve_plan() {
        if (!current_user_can('مراجعة_التحضير')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_assignment_action')) wp_send_json_error('Security check');

        global $wpdb;
        $plan_id = intval($_POST['plan_id']);
        $result = $wpdb->update("{$wpdb->prefix}sm_assignments",
            array('receiver_id' => get_current_user_id()), // Mark as approved by current coordinator
            array('id' => $plan_id, 'type' => 'lesson_plan')
        );

        if ($result) wp_send_json_success();
        else wp_send_json_error();
    }

    public function ajax_bulk_delete_users() {
        if (!current_user_can('إدارة_المستخدمين')) wp_send_json_error('Unauthorized');
        if (!wp_verify_nonce($_POST['nonce'], 'sm_teacher_action')) wp_send_json_error('Security check');

        $ids = array_map('intval', explode(',', $_POST['user_ids']));
        require_once(ABSPATH . 'wp-admin/includes/user.php');

        foreach ($ids as $id) {
            if ($id != get_current_user_id()) {
                wp_delete_user($id);
            }
        }
        wp_send_json_success();
    }

    public function handle_form_submission() {
        // Handle Hierarchical Violations Save
        if (isset($_POST['sm_save_hierarchical_violations']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                $processed = array();
                if (isset($_POST['h_viol']) && is_array($_POST['h_viol'])) {
                    foreach ($_POST['h_viol'] as $level => $items) {
                        $processed[$level] = array();
                        foreach ($items as $item) {
                            if (!empty($item['name'])) {
                                $code = !empty($item['code']) ? $item['code'] : 'V'.rand(100,999);
                                $processed[$level][$code] = array(
                                    'name' => sanitize_text_field($item['name']),
                                    'points' => intval($item['points']),
                                    'action' => sanitize_text_field($item['action'])
                                );
                            }
                        }
                    }
                }
                SM_Settings::save_hierarchical_violations($processed);
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Parent Call-in Request
        if (isset($_POST['sm_send_call_in']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_message_action')) {
            if (current_user_can('إدارة_أولياء_الأمور')) {
                $receiver_id = intval($_POST['receiver_id']);
                $message = "🔴 طلب استدعاء رسمي: " . sanitize_textarea_field($_POST['message']);
                SM_DB::send_message(get_current_user_id(), $receiver_id, $message);
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Generic User Update
        if (isset($_POST['sm_update_generic_user']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_user_action')) {
            if (current_user_can('إدارة_المستخدمين')) {
                $user_id = intval($_POST['edit_user_id']);
                $user_data = array(
                    'ID' => $user_id,
                    'user_email' => sanitize_email($_POST['user_email']),
                    'display_name' => sanitize_text_field($_POST['display_name'])
                );
                if (!empty($_POST['user_pass'])) {
                    $user_data['user_pass'] = $_POST['user_pass'];
                }
                wp_update_user($user_data);
                
                $u = new WP_User($user_id);
                $u->set_role(sanitize_text_field($_POST['user_role']));

                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Record Saving
        if (isset($_POST['sm_save_record']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_record_action')) {
            $record_id = SM_DB::add_record($_POST);
            if ($record_id) {
                SM_Notifications::send_violation_alert($record_id);
                $url = add_query_arg(array('sm_msg' => 'success', 'last_id' => $record_id), $_SERVER['REQUEST_URI']);
                wp_redirect($url);
                exit;
            }
        }

        // Handle Generic User Addition
        if (isset($_POST['sm_add_user']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_user_action')) {
            if (current_user_can('إدارة_المستخدمين')) {
                $user_data = array(
                    'user_login' => sanitize_user($_POST['user_login']),
                    'user_email' => sanitize_email($_POST['user_email']),
                    'display_name' => sanitize_text_field($_POST['display_name']),
                    'user_pass' => $_POST['user_pass'],
                    'role' => sanitize_text_field($_POST['user_role'])
                );
                wp_insert_user($user_data);
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Generic User Deletion
        if (isset($_POST['sm_delete_user']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_user_action')) {
            if (current_user_can('إدارة_المستخدمين')) {
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                wp_delete_user(intval($_POST['delete_user_id']));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Teacher Addition from Public Admin
        if (isset($_POST['sm_add_teacher']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_teacher_action')) {
            if (current_user_can('إدارة_المعلمين')) {
                $user_data = array(
                    'user_login' => sanitize_user($_POST['user_login']),
                    'user_email' => sanitize_email($_POST['user_email']),
                    'display_name' => sanitize_text_field($_POST['display_name']),
                    'user_pass' => $_POST['user_pass'],
                    'role' => 'sm_teacher'
                );
                $user_id = wp_insert_user($user_data);
                if (!is_wp_error($user_id)) {
                    update_user_meta($user_id, 'sm_teacher_id', sanitize_text_field($_POST['teacher_id']));
                    update_user_meta($user_id, 'sm_job_title', sanitize_text_field($_POST['job_title']));
                    update_user_meta($user_id, 'sm_phone', sanitize_text_field($_POST['phone']));
                    wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                    exit;
                }
            }
        }

        // Handle Teacher Update
        if (isset($_POST['sm_update_teacher']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_teacher_action')) {
            if (current_user_can('إدارة_المعلمين')) {
                $user_id = intval($_POST['edit_teacher_id']);
                $user_data = array(
                    'ID' => $user_id,
                    'user_email' => sanitize_email($_POST['user_email']),
                    'display_name' => sanitize_text_field($_POST['display_name'])
                );
                if (!empty($_POST['user_pass'])) {
                    $user_data['user_pass'] = $_POST['user_pass'];
                }
                wp_update_user($user_data);
                update_user_meta($user_id, 'sm_teacher_id', sanitize_text_field($_POST['teacher_id']));
                update_user_meta($user_id, 'sm_job_title', sanitize_text_field($_POST['job_title']));
                update_user_meta($user_id, 'sm_phone', sanitize_text_field($_POST['phone']));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Teacher Deletion
        if (isset($_POST['sm_delete_teacher']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_teacher_action')) {
            if (current_user_can('إدارة_المعلمين')) {
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                wp_delete_user(intval($_POST['delete_teacher_id']));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Record Update
        if (isset($_POST['sm_update_record']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_record_action')) {
            if (current_user_can('إدارة_المخالفات')) {
                SM_DB::update_record(intval($_POST['record_id']), $_POST);
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Student Addition from Public Admin
        if (isset($_POST['add_student']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_add_student')) {
            if (current_user_can('إدارة_الطلاب')) {
                $parent_user_id = !empty($_POST['parent_user_id']) ? intval($_POST['parent_user_id']) : null;
                $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
                SM_DB::add_student($_POST['name'], $_POST['class'], $_POST['email'], $_POST['code'], $parent_user_id, $teacher_id);
                wp_redirect(add_query_arg('sm_admin_msg', 'student_added', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Student Deletion from Public Admin
        if (isset($_POST['delete_student']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_add_student')) {
            if (current_user_can('إدارة_الطلاب')) {
                SM_DB::delete_student($_POST['delete_student_id']);
                wp_redirect(add_query_arg('sm_admin_msg', 'student_deleted', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Student Update from Public Admin
        if (isset($_POST['sm_update_student']) && wp_verify_nonce($_POST['sm_nonce'], 'sm_add_student')) {
            if (current_user_can('إدارة_الطلاب')) {
                SM_DB::update_student(intval($_POST['student_id']), $_POST);
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Backup Download
        if (isset($_POST['sm_download_backup']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                SM_Settings::record_backup_download();
                $data = SM_DB::get_backup_data();
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="sm_backup_'.date('Y-m-d').'.json"');
                echo $data;
                exit;
            }
        }

        // Handle Restore
        if (isset($_POST['sm_restore_backup']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام') && !empty($_FILES['backup_file']['tmp_name'])) {
                $json = file_get_contents($_FILES['backup_file']['tmp_name']);
                if (SM_DB::restore_backup($json)) {
                    SM_Settings::record_backup_import();
                    wp_redirect(add_query_arg('sm_admin_msg', 'restored', $_SERVER['REQUEST_URI']));
                    exit;
                }
            }
        }

        // Handle Academic Structure Save
        if (isset($_POST['sm_save_academic_structure']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                $academic_data = array(
                    'term_dates' => $_POST['term_dates'],
                    'academic_stages' => $_POST['academic_stages'],
                    'grades_count' => intval($_POST['grades_count']),
                    'active_grades' => isset($_POST['active_grades']) ? array_map('intval', $_POST['active_grades']) : array(),
                    'grade_sections' => $_POST['grade_sections'] ?? array(),
                    'sections_count' => intval($_POST['sections_count']),
                    'section_letters' => sanitize_text_field($_POST['section_letters'])
                );
                SM_Settings::save_academic_structure($academic_data);
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Unified Settings Save (School Info)
        if (isset($_POST['sm_save_settings_unified']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                SM_Settings::save_school_info(array(
                    'school_name' => sanitize_text_field($_POST['school_name']),
                    'school_principal_name' => sanitize_text_field($_POST['school_principal_name']),
                    'school_logo' => esc_url_raw($_POST['school_logo']),
                    'address' => sanitize_text_field($_POST['school_address']),
                    'email' => sanitize_email($_POST['school_email']),
                    'phone' => sanitize_text_field($_POST['school_phone']),
                    'working_schedule' => array(
                        'staff' => isset($_POST['work_staff']) ? array_map('sanitize_text_field', $_POST['work_staff']) : array(),
                        'students' => isset($_POST['work_students']) ? array_map('sanitize_text_field', $_POST['work_students']) : array()
                    )
                ));
                SM_Settings::save_academic_structure(array(
                    'terms_count' => intval($_POST['terms_count']),
                    'grades_count' => intval($_POST['grades_count']),
                    'grade_options' => sanitize_text_field($_POST['grade_options']),
                    'semester_start' => sanitize_text_field($_POST['semester_start']),
                    'semester_end' => sanitize_text_field($_POST['semester_end']),
                    'academic_stages' => sanitize_text_field($_POST['academic_stages'])
                ));
                SM_Settings::save_retention_settings(array(
                    'message_retention_days' => intval($_POST['message_retention_days'])
                ));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Appearance Settings Save
        if (isset($_POST['sm_save_appearance']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                SM_Settings::save_appearance(array(
                    'primary_color' => sanitize_hex_color($_POST['primary_color']),
                    'secondary_color' => sanitize_hex_color($_POST['secondary_color']),
                    'accent_color' => sanitize_hex_color($_POST['accent_color']),
                    'dark_color' => sanitize_hex_color($_POST['dark_color']),
                    'font_size' => sanitize_text_field($_POST['font_size']),
                    'border_radius' => sanitize_text_field($_POST['border_radius']),
                    'table_style' => sanitize_text_field($_POST['table_style']),
                    'button_style' => sanitize_text_field($_POST['button_style'])
                ));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Violation Settings Save
        if (isset($_POST['sm_save_violation_settings']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                $types_raw = explode("\n", str_replace("\r", "", $_POST['violation_types']));
                $types = array();
                foreach ($types_raw as $line) {
                    $parts = explode("|", $line);
                    if (count($parts) == 2) {
                        $types[trim($parts[0])] = trim($parts[1]);
                    }
                }
                if (!empty($types)) {
                    SM_Settings::save_violation_types($types);
                }
                SM_Settings::save_suggested_actions(array(
                    'low' => sanitize_textarea_field($_POST['suggested_low']),
                    'medium' => sanitize_textarea_field($_POST['suggested_medium']),
                    'high' => sanitize_textarea_field($_POST['suggested_high'])
                ));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Print Templates Save
        if (isset($_POST['sm_save_print_templates']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                update_option('sm_print_settings', array(
                    'header' => $_POST['print_header'], // Allowing HTML as requested for customization
                    'footer' => $_POST['print_footer'],
                    'custom_css' => $_POST['print_css']
                ));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Notifications Settings Save
        if (isset($_POST['sm_save_notif']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                SM_Settings::save_notifications(array(
                    'email_subject' => sanitize_text_field($_POST['email_subject']),
                    'email_template' => sanitize_textarea_field($_POST['email_template']),
                    'whatsapp_template' => sanitize_textarea_field($_POST['whatsapp_template']),
                    'internal_template' => sanitize_textarea_field($_POST['internal_template'])
                ));
                wp_redirect(add_query_arg('sm_admin_msg', 'settings_saved', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Full Reset
        if (isset($_POST['sm_full_reset']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_النظام')) {
                if ($_POST['reset_password'] === '1011996') {
                    SM_DB::delete_all_data();
                    wp_redirect(add_query_arg('sm_admin_msg', 'demo_deleted', $_SERVER['REQUEST_URI']));
                    exit;
                } else {
                    wp_redirect(add_query_arg('sm_admin_msg', 'error', $_SERVER['REQUEST_URI']));
                    exit;
                }
            }
        }

        // Handle CSV Upload (Students) - Configured for Excel Column Mapping (J, K, L) with Validation & Partial Support
        if (isset($_POST['sm_import_csv']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_الطلاب') && !empty($_FILES['csv_file']['tmp_name'])) {
                @set_time_limit(0);
                ini_set('auto_detect_line_endings', true);

                $results = array(
                    'total'   => 0,
                    'success' => 0,
                    'warning' => 0,
                    'error'   => 0,
                    'details' => array()
                );

                $handle = fopen($_FILES['csv_file']['tmp_name'], "r");

                // Detection & Skip BOM
                $bom = fread($handle, 3);
                if ($bom != "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Detect delimiter
                $first_line = fgets($handle);
                rewind($handle);
                $bom = fread($handle, 3);
                if ($bom != "\xEF\xBB\xBF") rewind($handle);

                $delimiters = [',', ';', "\t", '|'];
                $delimiter = ',';
                $max_count = -1;
                foreach ($delimiters as $d) {
                    $count = substr_count($first_line, $d);
                    if ($count > $max_count) {
                        $max_count = $count;
                        $delimiter = $d;
                    }
                }

                // Skip Header
                fgetcsv($handle, 0, $delimiter);

                $rows = array();
                $row_index = 2;
                while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                    $rows[] = array('data' => $data, 'index' => $row_index++);
                }
                fclose($handle);

                $next_sort_order = SM_DB::get_next_sort_order();

                foreach ($rows as $row_obj) {
                    $data = $row_obj['data'];
                    $row_index = $row_obj['index'];
                    $results['total']++;

                    // Attempt encoding conversion for Arabic (handles mixed encodings)
                    foreach ($data as $k => $v) {
                        $encoding = mb_detect_encoding($v, array('UTF-8', 'ISO-8859-6', 'ISO-8859-1'), true);
                        if ($encoding && $encoding != 'UTF-8') {
                            $data[$k] = mb_convert_encoding($v, 'UTF-8', $encoding);
                        }
                    }

                    // Mapping based on User Request (Excel Configuration):
                    // Column A (0): Full Name
                    // Column B (1): Grade / Class
                    // Column C (2): Section
                    // Column D (3): Student Nationality
                    // Column E (4): Guardian Email
                    // Column F (5): Guardian Phone Number

                    $full_display_name = isset($data[0]) ? trim($data[0]) : '';
                    $class_name        = isset($data[1]) ? trim($data[1]) : '';
                    $section           = isset($data[2]) ? trim($data[2]) : '';

                    $academic = SM_Settings::get_academic_structure();

                    // Normalize Grade format (e.g., "12" or "Grade 12" -> "الصف 12")
                    if (!empty($class_name)) {
                        $grade_number = preg_replace('/[^0-9]/', '', $class_name);
                        if (!empty($grade_number)) {
                            $class_name = 'الصف ' . $grade_number;
                            $grade_val = (int)$grade_number;

                            // Validate Grade against active grades
                            if (!in_array($grade_val, $academic['active_grades'])) {
                                $warnings[] = "الصف ($grade_number) غير مفعل في إعدادات الهيكل المدرسي.";
                            }

                            // Validate Section
                            if (!empty($section)) {
                                $gs = $academic['grade_sections'][$grade_val] ?? array('count' => $academic['sections_count'], 'letters' => $academic['section_letters']);
                                $allowed_letters = array_map('trim', explode(',', $gs['letters']));
                                if (!in_array($section, $allowed_letters)) {
                                    $warnings[] = "الشعبة ($section) غير معرفة للصف ($grade_number).";
                                }
                            }
                        }
                    }
                    $nationality       = isset($data[3]) ? trim($data[3]) : '';
                    $guardian_email    = isset($data[4]) ? trim($data[4]) : '';
                    $guardian_phone    = isset($data[5]) ? trim($data[5]) : '';

                    $errors = array();
                    $warnings = array();

                    if (empty($full_display_name)) {
                        $errors[] = "الاسم الكامل مفقود في السطر " . $row_index;
                    }

                    if (empty($class_name)) {
                        $errors[] = "الصف الدراسي مفقود في السطر " . $row_index;
                    }

                    if (!empty($errors)) {
                        $results['error']++;
                        foreach ($errors as $err) $results['details'][] = array('type' => 'error', 'msg' => $err);
                    } else {
                        // Match against existing students (Name, Grade, Section)
                        $existing_id = SM_DB::student_exists($full_display_name, $class_name, $section);

                        if ($existing_id) {
                            $results['success']++;
                            // We don't increment sort_order here as we are skipping
                            continue;
                        }

                        $extra = array(
                            'guardian_phone' => $guardian_phone,
                            'nationality' => $nationality,
                            'sort_order' => $next_sort_order++
                        );
                        $imported_id = SM_DB::add_student($full_display_name, $class_name, $guardian_email, '', null, null, $section, $extra);
                        if ($imported_id) {
                            $results['success']++;
                            if (!empty($warnings)) {
                                $results['warning']++;
                                foreach ($warnings as $warn) $results['details'][] = array('type' => 'warning', 'msg' => $warn);
                            }
                        } else {
                            $results['error']++;
                            $results['details'][] = array('type' => 'error', 'msg' => "فشل حفظ البيانات في قاعدة البيانات للسطر " . $row_index);
                        }
                    }
                }

                SM_Logger::log('استيراد طلاب (جماعي)', "تم استيراد {$results['success']} طالب بنجاح من أصل {$results['total']}");
                set_transient('sm_import_results_' . get_current_user_id(), $results, HOUR_IN_SECONDS);
                wp_redirect(add_query_arg('sm_admin_msg', 'import_completed', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Teacher CSV Upload
        if (isset($_POST['sm_import_teachers_csv']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_المعلمين') && !empty($_FILES['csv_file']['tmp_name'])) {
                $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
                $header = fgetcsv($handle); // skip header
                $count = 0;
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) >= 3) {
                        // username, email, name, teacher_id, job_title, phone, pass
                        $user_id = wp_insert_user(array(
                            'user_login' => $data[0],
                            'user_email' => $data[1],
                            'display_name' => $data[2],
                            'user_pass' => isset($data[6]) ? $data[6] : wp_generate_password(),
                            'role' => 'sm_teacher'
                        ));
                        if (!is_wp_error($user_id)) {
                            $count++;
                            update_user_meta($user_id, 'sm_teacher_id', isset($data[3]) ? $data[3] : '');
                            update_user_meta($user_id, 'sm_job_title', isset($data[4]) ? $data[4] : '');
                            update_user_meta($user_id, 'sm_phone', isset($data[5]) ? $data[5] : '');
                        }
                    }
                }
                fclose($handle);
                SM_Logger::log('استيراد معلمين (جماعي)', "تم استيراد ($count) معلم بنجاح.");
                wp_redirect(add_query_arg('sm_admin_msg', 'csv_imported', $_SERVER['REQUEST_URI']));
                exit;
            }
        }

        // Handle Violation CSV Upload
        if (isset($_POST['sm_import_violations_csv']) && wp_verify_nonce($_POST['sm_admin_nonce'], 'sm_admin_action')) {
            if (current_user_can('إدارة_المخالفات')) {
                $handle = fopen($_FILES['csv_file']['tmp_name'], "r");
                $header = fgetcsv($handle); // skip header
                $count = 0;
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) >= 4) {
                        // code, type, severity, details, action, reward
                        $student = SM_DB::get_student_by_code($data[0]);
                        if ($student) {
                            $rid = SM_DB::add_record(array(
                                'student_id' => $student->id,
                                'type' => $data[1],
                                'severity' => $data[2],
                                'details' => $data[3],
                                'action_taken' => isset($data[4]) ? $data[4] : '',
                                'reward_penalty' => isset($data[5]) ? $data[5] : ''
                            ), true); // Skip individual logs
                            if ($rid) {
                                $count++;
                                SM_Notifications::send_violation_alert($rid);
                            }
                        }
                    }
                }
                fclose($handle);
                SM_Logger::log('استيراد مخالفات (جماعي)', "تم استيراد ($count) مخالفة بنجاح.");
                wp_redirect(add_query_arg('sm_admin_msg', 'csv_imported', $_SERVER['REQUEST_URI']));
                exit;
            }
        }
    }
}
