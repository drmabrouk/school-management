<?php

class SM_Logger {
    public static function log($action, $details = '') {
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}sm_logs",
            array(
                'user_id' => get_current_user_id(),
                'action' => sanitize_text_field($action),
                'details' => sanitize_textarea_field($details)
            )
        );
    }

    public static function get_logs($limit = 100) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, u.display_name FROM {$wpdb->prefix}sm_logs l LEFT JOIN {$wpdb->base_prefix}users u ON l.user_id = u.ID ORDER BY l.created_at DESC LIMIT %d",
            $limit
        ));
    }
}
