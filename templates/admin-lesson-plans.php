<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-lesson-plans-container" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 style="margin: 0; font-weight: 800;">تحضير الدروس اليومي</h3>
        <?php if ($is_teacher): ?>
            <button onclick="document.getElementById('add-plan-modal').style.display='flex'" class="sm-btn" style="width: auto;">+ إضافة تحضير جديد</button>
        <?php endif; ?>
    </div>

    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>المعلم</th>
                    <th>العنوان</th>
                    <th>المرفق</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Plans logic
                $plans = array();
                if ($is_coordinator) {
                    // Show all lesson plans for review (Maybe filter by department later)
                    global $wpdb;
                    $plans = $wpdb->get_results("SELECT a.*, u.display_name as sender_name FROM {$wpdb->prefix}sm_assignments a JOIN {$wpdb->prefix}users u ON a.sender_id = u.ID WHERE a.type = 'lesson_plan' ORDER BY a.created_at DESC");
                } elseif ($is_teacher) {
                    $plans = SM_DB::get_sent_assignments($user->ID); // Need to filter by type lesson_plan
                    $plans = array_filter($plans, function($p) { return $p->type === 'lesson_plan'; });
                }

                if (empty($plans)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px;">لا يوجد تحضيرات دروس حالياً.</td></tr>
                <?php else: foreach($plans as $p): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($p->created_at)); ?></td>
                        <td><?php echo esc_html($p->sender_name ?? $user->display_name); ?></td>
                        <td style="font-weight: 700;"><?php echo esc_html($p->title); ?></td>
                        <td>
                            <?php if ($p->file_url): ?>
                                <a href="<?php echo esc_url($p->file_url); ?>" target="_blank" class="sm-btn sm-btn-outline" style="font-size: 11px; padding: 4px 8px;">فتح التحضير</a>
                            <?php else: ?>
                                ---
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p->receiver_id == 0): // For plans, receiver_id 0 means pending review ?>
                                <span class="sm-badge sm-badge-low">قيد المراجعة</span>
                            <?php else: ?>
                                <span class="sm-badge sm-badge-high" style="background:#38a169;">تمت المراجعة</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_coordinator && $p->receiver_id == 0): ?>
                                <button onclick="approvePlan(<?php echo $p->id; ?>)" class="sm-btn" style="font-size: 11px; padding: 4px 8px; background:#38a169;">اعتماد</button>
                            <?php endif; ?>
                            <button onclick="alert('الوصف:\n' + <?php echo json_encode($p->description); ?>)" class="sm-btn sm-btn-outline" style="font-size: 11px; padding: 4px 8px;">عرض</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Plan Modal -->
<div id="add-plan-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 600px;">
        <div class="sm-modal-header">
            <h3>تقديم تحضير درس</h3>
            <button class="sm-modal-close" onclick="document.getElementById('add-plan-modal').style.display='none'">&times;</button>
        </div>
        <form id="add-plan-form">
            <?php wp_nonce_field('sm_assignment_action', 'sm_nonce'); ?>
            <input type="hidden" name="type" value="lesson_plan">
            <div class="sm-form-group">
                <label class="sm-label">عنوان الدرس:</label>
                <input type="text" name="title" class="sm-input" required>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">الأهداف / الملاحظات:</label>
                <textarea name="description" class="sm-textarea" rows="4"></textarea>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">ملف التحضير (PDF/Word):</label>
                <div style="display:flex; gap:10px;">
                    <input type="text" name="file_url" id="plan_file_url" class="sm-input" required>
                    <button type="button" onclick="smOpenMediaUploader('plan_file_url')" class="sm-btn" style="width:auto; font-size:12px; background:var(--sm-secondary-color);">رفع ملف</button>
                </div>
            </div>
            <button type="submit" class="sm-btn">تقديم التحضير للمنسق</button>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('add-plan-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'sm_add_assignment_ajax');
            formData.append('receiver_id', '0'); // 0 = Pending review by any coordinator
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    smShowNotification('تم تقديم التحضير بنجاح');
                    setTimeout(() => location.reload(), 500);
                }
            });
        });
    }
})();

function approvePlan(id) {
    if (!confirm('هل أنت متأكد من اعتماد هذا التحضير؟')) return;
    const formData = new FormData();
    formData.append('action', 'sm_approve_plan_ajax');
    formData.append('plan_id', id);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_assignment_action"); ?>');
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم اعتماد التحضير');
            setTimeout(() => location.reload(), 500);
        }
    });
}
</script>
