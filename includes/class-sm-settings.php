<?php

class SM_Settings {
    public static function get_violation_types() {
        $default = array(
            'behavior' => 'سلوك',
            'lateness' => 'تأخر',
            'absence' => 'غياب',
            'other' => 'أخرى'
        );
        return get_option('sm_violation_types', $default);
    }

    public static function get_severities() {
        return array(
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'خطيرة'
        );
    }

    public static function save_violation_types($types) {
        update_option('sm_violation_types', $types);
    }

    public static function get_appearance() {
        $default = array(
            'primary_color' => '#F63049',
            'secondary_color' => '#D02752',
            'accent_color' => '#8A244B',
            'dark_color' => '#111F35',
            'font_size' => '15px',
            'border_radius' => '12px',
            'table_style' => 'modern',
            'button_style' => 'flat'
        );
        return wp_parse_args(get_option('sm_appearance', array()), $default);
    }

    public static function save_appearance($data) {
        update_option('sm_appearance', $data);
    }

    public static function get_notifications() {
        $default = array(
            'email_subject' => 'تنبيه بخصوص سلوك الطالب: {student_name}',
            'email_template' => "تم تسجيل ملاحظة بخصوص الطالب: {student_name}\nنوع المخالفة: {type}\nالحدة: {severity}\nالتفاصيل: {details}\nالإجراء المتخذ: {action_taken}",
            'whatsapp_template' => "تنبيه من المدرسة: تم تسجيل ملاحظة سلوكية بحق الطالب {student_name}. نوع الملاحظة: {type}. تفاصيل: {details}. الإجراء: {action_taken}",
            'internal_template' => "إشعار نظام: تم تسجيل مخالفة {type} للطالب {student_name}. الرجاء مراجعة سجل الطالب."
        );
        return get_option('sm_notification_settings', $default);
    }

    public static function save_notifications($data) {
        update_option('sm_notification_settings', $data);
    }

    public static function get_school_info() {
        $default = array(
            'school_name' => 'مدرستي النموذجية',
            'school_logo' => '',
            'address' => 'الرياض، المملكة العربية السعودية',
            'email' => 'info@school.edu',
            'phone' => '0123456789',
            'map_link' => '',
            'extra_details' => ''
        );
        return get_option('sm_school_info', $default);
    }

    public static function save_school_info($data) {
        update_option('sm_school_info', $data);
    }

    public static function get_academic_structure() {
        $default = array(
            'terms_count' => 3,
            'term_dates' => array(
                'term1' => array('start' => '', 'end' => ''),
                'term2' => array('start' => '', 'end' => ''),
                'term3' => array('start' => '', 'end' => '')
            ),
            'grades_count' => 12,
            'active_grades' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
            'grade_sections' => array(), // Per-grade sections: [grade_num => [count => 5, letters => "أ, ب..."]]
            'sections_count' => 5,
            'section_letters' => "أ, ب, ج, د, هـ",
            'academic_stages' => array(
                array('name' => 'المرحلة الابتدائية', 'start' => 1, 'end' => 4),
                array('name' => 'المرحلة المتوسطة', 'start' => 5, 'end' => 8),
                array('name' => 'المرحلة الثانوية', 'start' => 9, 'end' => 12)
            )
        );
        return wp_parse_args(get_option('sm_academic_structure', array()), $default);
    }

    public static function save_academic_structure($data) {
        update_option('sm_academic_structure', $data);
    }

    /**
     * Standardized Naming for Grades and Sections
     */
    public static function format_grade_name($grade, $section = '', $format = 'full') {
        if (empty($grade)) return '---';

        // Remove "الصف" prefix if it exists in data
        $grade_num = str_replace('الصف ', '', $grade);

        if ($format === 'short') {
            return trim($grade_num . ' ' . $section);
        }

        // Full format: "Grade + Number + Section"
        $output = 'الصف ' . $grade_num;
        if (!empty($section)) {
            $output .= ' شعبة ' . $section;
        }
        return $output;
    }

    public static function get_retention_settings() {
        $default = array(
            'message_retention_days' => 90
        );
        return get_option('sm_retention_settings', $default);
    }

    public static function save_retention_settings($data) {
        update_option('sm_retention_settings', $data);
    }

    public static function record_backup_download() {
        update_option('sm_last_backup_download', current_time('mysql'));
    }

    public static function record_backup_import() {
        update_option('sm_last_backup_import', current_time('mysql'));
    }

    public static function get_last_backup_info() {
        return array(
            'export' => get_option('sm_last_backup_download', 'لم يتم التصدير مسبقاً'),
            'import' => get_option('sm_last_backup_import', 'لم يتم الاستيراد مسبقاً')
        );
    }

    public static function get_suggested_actions() {
        $default = array(
            'low' => "تنبيه شفوي\nتسجيل ملاحظة\nنصيحة تربوية",
            'medium' => "إنذار خطي\nاستدعاء ولي أمر\nحسم درجات سلوك",
            'high' => "فصل مؤقت\nمجلس انضباط\nتعهد خطي شديد"
        );
        return get_option('sm_suggested_actions', $default);
    }

    public static function save_suggested_actions($actions) {
        update_option('sm_suggested_actions', $actions);
    }
}
