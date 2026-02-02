<?php if (!defined('ABSPATH')) exit; ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h3 style="margin:0; border:none; padding:0;">إدارة مستخدمي النظام</h3>
    <button onclick="document.getElementById('add-user-modal').style.display='flex'" class="sm-btn" style="width:auto;">+ إضافة مستخدم جديد</button>
</div>

<div class="sm-table-container">
    <table class="sm-table">
        <thead>
            <tr>
                <th>المستخدم</th>
                <th>البريد الإلكتروني</th>
                <th>الرتبة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (get_users() as $u): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php echo get_avatar($u->ID, 32, '', '', array('style' => 'border-radius:50%;')); ?>
                            <div>
                                <div style="font-weight: 700;"><?php echo esc_html($u->display_name); ?></div>
                                <div style="font-size:10px; color:#a0aec0;">@<?php echo esc_html($u->user_login); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo esc_html($u->user_email); ?></td>
                    <td>
                        <span class="sm-badge sm-badge-low">
                            <?php
                            $role_map = array(
                                'administrator' => 'الإدارة المركزية (المطور)',
                                'sm_system_admin' => 'مدير النظام التقني',
                                'sm_principal' => 'مدير المدرسة',
                                'sm_supervisor' => 'مشرف تربوي',
                                'sm_coordinator' => 'منسق مادة',
                                'sm_teacher' => 'معلم',
                                'sm_student' => 'طالب',
                                'sm_parent' => 'ولي أمر'
                            );
                            $display_roles = array_map(function($r) use ($role_map) { return $role_map[$r] ?? $r; }, $u->roles);
                            echo implode(', ', $display_roles);
                            ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <button onclick='editSmGenericUser(<?php echo json_encode(array("id"=>$u->ID, "name"=>$u->display_name, "email"=>$u->user_email, "login"=>$u->user_login, "role"=>$u->roles[0])); ?>)' class="sm-btn" style="background:#edf2f7; color:#2d3748; padding:5px 10px; width:auto; font-size:11px;">تعديل</button>
                            <?php if ($u->ID != get_current_user_id()): ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('حذف هذا المستخدم نهائياً؟')">
                                    <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
                                    <input type="hidden" name="delete_user_id" value="<?php echo $u->ID; ?>">
                                    <button type="submit" name="sm_delete_user" class="sm-btn" style="background:#e53e3e; padding:5px 10px; width:auto; font-size:11px;">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<div id="add-user-modal" class="sm-modal-overlay">
    <div class="sm-modal-content">
        <div class="sm-modal-header">
            <h3>إضافة مستخدم جديد</h3>
            <button class="sm-modal-close" onclick="document.getElementById('add-user-modal').style.display='none'">&times;</button>
        </div>
        <form id="add-user-form">
            <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="sm-form-group">
                    <label class="sm-label">الاسم الكامل:</label>
                    <input type="text" name="display_name" class="sm-input" required>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">اسم المستخدم (Login):</label>
                    <input type="text" name="user_login" class="sm-input" required>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">البريد الإلكتروني (اختياري):</label>
                    <input type="email" name="user_email" class="sm-input">
                    <div style="font-size: 10px; color: #718096; margin-top: 5px;">سيتم إنشاء بريد تلقائي إذا ترك فارغاً.</div>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">الرتبة:</label>
                    <select name="user_role" class="sm-select">
                        <option value="sm_system_admin">مدير النظام التقني</option>
                        <option value="sm_principal">مدير المدرسة</option>
                        <option value="sm_supervisor">مشرف تربوي</option>
                        <option value="sm_coordinator">منسق مادة</option>
                        <option value="sm_teacher">معلم</option>
                        <option value="sm_student">طالب</option>
                    </select>
                </div>
                <div class="sm-form-group" style="grid-column: span 2;">
                    <label class="sm-label">كلمة المرور:</label>
                    <input type="password" name="user_pass" class="sm-input" required>
                </div>
            </div>
            <button type="submit" class="sm-btn" style="margin-top:20px; width: 100%;">إنشاء الحساب الآن</button>
        </form>
    </div>
</div>

<div id="edit-user-modal" class="sm-modal-overlay">
    <div class="sm-modal-content">
        <div class="sm-modal-header">
            <h3>تعديل بيانات المستخدم</h3>
            <button class="sm-modal-close" onclick="document.getElementById('edit-user-modal').style.display='none'">&times;</button>
        </div>
        <form id="edit-user-form">
            <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
            <input type="hidden" name="edit_user_id" id="edit_u_id">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="sm-form-group">
                    <label class="sm-label">الاسم الكامل:</label>
                    <input type="text" name="display_name" id="edit_u_name" class="sm-input" required>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">البريد الإلكتروني:</label>
                    <input type="email" name="user_email" id="edit_u_email" class="sm-input">
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">الرتبة:</label>
                    <select name="user_role" id="edit_u_role" class="sm-select">
                        <option value="sm_system_admin">مدير النظام التقني</option>
                        <option value="sm_principal">مدير المدرسة</option>
                        <option value="sm_supervisor">مشرف تربوي</option>
                        <option value="sm_coordinator">منسق مادة</option>
                        <option value="sm_teacher">معلم</option>
                        <option value="sm_student">طالب</option>
                    </select>
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">كلمة مرور جديدة (اختياري):</label>
                    <input type="password" name="user_pass" class="sm-input" placeholder="اتركه فارغاً لعدم التغيير">
                </div>
            </div>
            <button type="submit" class="sm-btn" style="margin-top:20px; width: 100%;">حفظ التغييرات</button>
        </form>
    </div>
</div>

<script>
(function() {
    window.editSmGenericUser = function(u) {
        document.getElementById('edit_u_id').value = u.id;
        document.getElementById('edit_u_name').value = u.name;
        document.getElementById('edit_u_email').value = u.email;
        document.getElementById('edit_u_role').value = u.role;
        document.getElementById('edit-user-modal').style.display = 'flex';
    };

    const addForm = document.getElementById('add-user-form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'sm_add_user_ajax'); // Need to implement this
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    smShowNotification('تمت إضافة المستخدم');
                    setTimeout(() => location.reload(), 500);
                }
            });
        });
    }

    const editForm = document.getElementById('edit-user-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'sm_update_generic_user_ajax'); // Need to implement this
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    smShowNotification('تم تحديث المستخدم');
                    setTimeout(() => location.reload(), 500);
                }
            });
        });
    }
})();
</script>
