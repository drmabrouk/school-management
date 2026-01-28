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
            'primary_color' => '#0073aa',
            'font_size' => '15px'
        );
        return get_option('sm_appearance', $default);
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
            'grades_count' => 12,
            'grade_options' => "أ, ب, ج",
            'semester_start' => '',
            'semester_end' => '',
            'academic_stages' => 'Primary, Middle, High'
        );
        return get_option('sm_academic_structure', $default);
    }

    public static function save_academic_structure($data) {
        update_option('sm_academic_structure', $data);
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
