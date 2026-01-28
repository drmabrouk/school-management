<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-content-wrapper" dir="rtl">
    <h1>نظام إدارة المدرسة - لوحة التحكم</h1>
    <hr>

    <?php if (current_user_can('إدارة_المخالفات')): ?>
        <?php include SM_PLUGIN_DIR . 'templates/public-dashboard-summary.php'; ?>
    <?php else: ?>
        <div class="welcome-panel" style="padding: 20px; margin-top: 20px;">
            <h2>أهلاً بك، <?php echo wp_get_current_user()->display_name; ?></h2>
            <p>لديك صلاحية الوصول المحدود لنظام إدارة المدرسة. استخدم القائمة الجانبية للوصول للوظائف المتاحة لك.</p>
        </div>
    <?php endif; ?>
    
    <div style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
        <?php if (current_user_can('إدارة_الطلاب')): ?>
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3>إدارة النظام</h3>
            <p>يمكنك إضافة طلاب جدد، إدارة المعلمين، واستعراض كافة السجلات.</p>
            <a href="admin.php?page=sm-students" class="button button-primary">إدارة الطلاب</a>
        </div>
        <?php endif; ?>

        <?php if (current_user_can('تسجيل_مخالفة')): ?>
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3>تسجيل سريع</h3>
            <p>استخدم هذا القسم لتسجيل مخالفة جديدة بسرعة أو استخدام ماسح الباركود.</p>
            <a href="admin.php?page=sm-record-violation" class="button button-primary">تسجيل مخالفة الآن</a>
        </div>
        <?php endif; ?>
        
        <?php if (current_user_can('manage_options')): ?>
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h3>الأكواد المختصرة للمطور</h3>
            <ul style="list-style: disc; padding-right: 20px;">
                <li><code>[sm_login]</code> - نموذج تسجيل الدخول بالعربية.</li>
                <li><code>[sm_admin]</code> - لوحة الإدارة الشاملة (Frontend).</li>
            </ul>
            <p style="font-size: 0.9em; color: #666;">تنبيه: يتم توجيه المستخدمين تلقائياً إلى لوحة التحكم بعد تسجيل الدخول.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
