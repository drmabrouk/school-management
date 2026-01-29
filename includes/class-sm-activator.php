<?php

class SM_Activator {

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_students = $wpdb->prefix . 'sm_students';
        $table_records = $wpdb->prefix . 'sm_records';
        $table_logs = $wpdb->prefix . 'sm_logs';
        $table_messages = $wpdb->prefix . 'sm_messages';

        $sql = "CREATE TABLE $table_students (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            class_name varchar(100) NOT NULL,
            section varchar(50) DEFAULT '',
            parent_email varchar(100),
            guardian_phone varchar(50) DEFAULT '',
            nationality varchar(100) DEFAULT '',
            registration_date date DEFAULT NULL,
            student_code varchar(50),
            parent_user_id bigint(20) DEFAULT NULL,
            teacher_id bigint(20) DEFAULT NULL,
            photo_url varchar(255) DEFAULT '',
            PRIMARY KEY  (id),
            KEY student_code (student_code),
            KEY teacher_id (teacher_id)
        ) $charset_collate;

        CREATE TABLE $table_records (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            student_id bigint(20) NOT NULL,
            teacher_id bigint(20) NOT NULL,
            type varchar(100) NOT NULL,
            classification varchar(100) DEFAULT 'general',
            severity varchar(50) NOT NULL,
            details text NOT NULL,
            action_taken text,
            reward_penalty text,
            status varchar(20) DEFAULT 'accepted' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY student_id (student_id),
            KEY teacher_id (teacher_id),
            KEY status (status)
        ) $charset_collate;

        CREATE TABLE $table_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action text NOT NULL,
            details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;

        CREATE TABLE $table_messages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sender_id bigint(20) NOT NULL,
            receiver_id bigint(20) NOT NULL,
            student_id bigint(20) DEFAULT NULL,
            message text NOT NULL,
            is_read tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY sender_id (sender_id),
            KEY receiver_id (receiver_id)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}sm_confiscated_items (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            student_id bigint(20) NOT NULL,
            item_name varchar(255) NOT NULL,
            holding_period int(11) DEFAULT 30,
            status varchar(50) DEFAULT 'held',
            is_returnable tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY student_id (student_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        self::add_custom_roles();
        self::seed_demo_data();
        self::create_default_pages();
        self::cleanup_legacy_pages();
        self::migrate_old_roles();
    }

    private static function cleanup_legacy_pages() {
        $legacy_page = get_page_by_path('sm-system');
        if ($legacy_page) {
            wp_delete_post($legacy_page->ID, true);
        }
    }

    private static function create_default_pages() {
        $pages = array(
            'sm-login' => array(
                'title'   => 'تسجيل الدخول',
                'content' => '[sm_login]',
            ),
            'sm-admin' => array(
                'title'   => 'لوحة التحكم المدرسية',
                'content' => '[sm_admin]',
            ),
        );

        foreach ($pages as $slug => $page_data) {
            $page_exists = get_page_by_path($slug);
            if (!$page_exists) {
                wp_insert_post(array(
                    'post_title'   => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_name'    => $slug,
                ));
            }
        }
    }

    public static function add_custom_roles() {
        // Remove old roles to clean up duplicates and English names
        remove_role('school_admin');
        remove_role('discipline_officer');
        remove_role('sm_school_admin');
        remove_role('sm_discipline_officer');
        remove_role('sm_teacher');
        remove_role('sm_parent');

        // Unified Arabic Capabilities
        $caps = array(
            'system_admin' => 'إدارة_النظام',
            'user_mgmt'    => 'إدارة_المستخدمين',
            'student_mgmt' => 'إدارة_الطلاب',
            'teacher_mgmt' => 'إدارة_المعلمين',
            'parent_mgmt'  => 'إدارة_أولياء_الأمور',
            'record_mgmt'  => 'إدارة_المخالفات',
            'record_add'   => 'تسجيل_مخالفة',
            'printing'     => 'طباعة_التقارير',
            'view_own'     => 'عرض_تقارير_الأبناء'
        );

        // 1. مدير النظام (System Administrator) - Can be mapped to Administrator
        $admin = get_role('administrator');
        if ($admin) {
            foreach ($caps as $cap) {
                $admin->add_cap($cap);
            }
        }

        // 2. مدير المدرسة (School Administrator)
        add_role('sm_school_admin', 'مدير المدرسة', array('read' => true));
        $school_admin = get_role('sm_school_admin');
        if ($school_admin) {
            $school_admin->add_cap($caps['student_mgmt']);
            $school_admin->add_cap($caps['teacher_mgmt']);
            $school_admin->add_cap($caps['parent_mgmt']);
            $school_admin->add_cap($caps['record_mgmt']);
            $school_admin->add_cap($caps['record_add']);
            $school_admin->add_cap($caps['printing']);
        }

        // 3. مسؤول الانضباط (Discipline Officer)
        add_role('sm_discipline_officer', 'مسؤول الانضباط', array('read' => true));
        $officer = get_role('sm_discipline_officer');
        if ($officer) {
            $officer->add_cap($caps['student_mgmt']);
            $officer->add_cap($caps['teacher_mgmt']);
            $officer->add_cap($caps['parent_mgmt']);
            $officer->add_cap($caps['record_mgmt']);
            $officer->add_cap($caps['record_add']);
            $officer->add_cap($caps['printing']);
        }

        // 4. معلم (Teacher)
        add_role('sm_teacher', 'معلم', array('read' => true));
        $teacher = get_role('sm_teacher');
        if ($teacher) {
            $teacher->add_cap($caps['record_add']);
            $teacher->add_cap($caps['student_mgmt']); // Access restricted via logic
        }

        // 5. ولي أمر (Parent)
        add_role('sm_parent', 'ولي أمر', array('read' => true));
        $parent = get_role('sm_parent');
        if ($parent) {
            $parent->add_cap($caps['view_own']);
        }
    }

    public static function migrate_old_roles() {
        $migration_map = array(
            'discipline_officer' => 'sm_discipline_officer',
            'school_admin'       => 'sm_school_admin',
        );

        foreach ($migration_map as $old_slug => $new_slug) {
            $users = get_users(array('role' => $old_slug));
            foreach ($users as $user) {
                $user->remove_role($old_slug);
                $user->add_role($new_slug);
            }
        }
    }

    private static function seed_demo_data() {
        global $wpdb;
        $table_students = $wpdb->prefix . 'sm_students';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_students");
        if ($count > 0) return;

        $demo_students = array(
            array('name' => 'أحمد محمد', 'class_name' => 'الصف الأول', 'parent_email' => 'parent1@example.com', 'student_code' => 'STU001'),
            array('name' => 'سارة علي', 'class_name' => 'الصف الأول', 'parent_email' => 'parent2@example.com', 'student_code' => 'STU002'),
            array('name' => 'خالد محمود', 'class_name' => 'الصف الثاني', 'parent_email' => 'parent3@example.com', 'student_code' => 'STU003'),
            array('name' => 'ليلى يوسف', 'class_name' => 'الصف الثاني', 'parent_email' => 'parent4@example.com', 'student_code' => 'STU004'),
            array('name' => 'عمر حسن', 'class_name' => 'الصف الثالث', 'parent_email' => 'parent5@example.com', 'student_code' => 'STU005'),
            array('name' => 'مريم إبراهيم', 'class_name' => 'الصف الثالث', 'parent_email' => 'parent6@example.com', 'student_code' => 'STU006'),
            array('name' => 'ياسين كمال', 'class_name' => 'الصف الرابع', 'parent_email' => 'parent7@example.com', 'student_code' => 'STU007'),
            array('name' => 'نور الهدى', 'class_name' => 'الصف الرابع', 'parent_email' => 'parent8@example.com', 'student_code' => 'STU008'),
            array('name' => 'عبد الله فهد', 'class_name' => 'الصف الخامس', 'parent_email' => 'parent9@example.com', 'student_code' => 'STU009'),
            array('name' => 'هند سعادة', 'class_name' => 'الصف الخامس', 'parent_email' => 'parent10@example.com', 'student_code' => 'STU010'),
        );

        foreach ($demo_students as $student) {
            $wpdb->insert($table_students, $student);
        }
    }
}
