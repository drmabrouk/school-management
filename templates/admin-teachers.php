<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-content-wrapper" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="margin:0; border:none; padding:0;">إدارة شؤون المعلمين</h3>
        <?php if (current_user_can('إدارة_المعلمين')): ?>
            <div style="display:flex; gap:10px;">
                <button onclick="document.getElementById('teacher-csv-import-form').style.display='block'" class="sm-btn" style="width:auto; background:var(--sm-secondary-color);">استيراد جماعي (CSV)</button>
                <button onclick="document.getElementById('add-teacher-modal').style.display='flex'" class="sm-btn" style="width:auto;">+ إضافة معلم جديد</button>
            </div>
        <?php endif; ?>
    </div>

    <div id="teacher-csv-import-form" style="display:none; background: #f8fafc; padding: 30px; border: 2px dashed #cbd5e0; border-radius: 12px; margin-bottom: 30px;">
        <h3 style="margin-top:0; color:var(--sm-secondary-color);">دليل استيراد المعلمين (CSV)</h3>
        
        <div style="background:#fff; padding:15px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
            <p style="font-size:13px; font-weight:700; margin-bottom:10px;">هيكل ملف المعلمين الصحيح:</p>
            <table style="width:100%; font-size:11px; border-collapse:collapse; text-align:center;">
                <thead>
                    <tr style="background:#edf2f7;">
                        <th style="border:1px solid #cbd5e0; padding:5px;">اسم المستخدم</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">البريد</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الاسم الكامل</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الكود الوظيفي</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">المسمى</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">رقم الجوال</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">كلمة المرور</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border:1px solid #cbd5e0; padding:5px;">teacher_ali</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">ali@school.com</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">علي محمود</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">T101</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">معلم رياضة</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">050000000</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">123456</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('sm_admin_action', 'sm_admin_nonce'); ?>
            <div class="sm-form-group">
                <label class="sm-label">اختر ملف CSV للمعلمين:</label>
                <input type="file" name="csv_file" accept=".csv" required>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="sm_import_teachers_csv" class="sm-btn" style="width:auto; background:#27ae60;">استيراد القائمة الآن</button>
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="sm-btn" style="width:auto; background:var(--sm-text-gray);">إلغاء</button>
            </div>
        </form>
    </div>

    <div style="background: white; padding: 30px; border: 1px solid var(--sm-border-color); border-radius: var(--sm-radius); margin-bottom: 30px; box-shadow: var(--sm-shadow);">
        <form method="get" style="display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: end;">
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">بحث عن معلم بالاسم أو البريد أو الكود:</label>
                <input type="text" name="teacher_search" class="sm-input" value="<?php echo esc_attr(isset($_GET['teacher_search']) ? $_GET['teacher_search'] : ''); ?>" placeholder="أدخل بيانات المعلم...">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="sm-btn">بدء البحث</button>
                <a href="<?php echo remove_query_arg('teacher_search'); ?>" class="sm-btn sm-btn-outline" style="text-decoration:none;">عرض الكل</a>
            </div>
        </form>
    </div>

    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th>كود المعلم</th>
                    <th>الاسم الكامل</th>
                    <th>المسمى الوظيفي</th>
                    <th>رقم التواصل</th>
                    <th>البريد الإلكتروني</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $args = array('role' => 'sm_teacher');
                if (!empty($_GET['teacher_search'])) {
                    $args['search'] = '*' . esc_attr($_GET['teacher_search']) . '*';
                    $args['search_columns'] = array('user_login', 'display_name', 'user_email');
                }
                $teachers = get_users($args);
                if (empty($teachers)): ?>
                    <tr><td colspan="6" style="padding: 40px; text-align: center;">لا يوجد معلمون مسجلون حالياً.</td></tr>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: var(--sm-primary-color);"><?php echo esc_html(get_user_meta($teacher->ID, 'sm_teacher_id', true)); ?></td>
                            <td style="font-weight: 800; color: var(--sm-dark-color);"><?php echo esc_html($teacher->display_name); ?></td>
                            <td><span class="sm-badge sm-badge-low"><?php echo esc_html(get_user_meta($teacher->ID, 'sm_job_title', true)); ?></span></td>
                            <td dir="ltr" style="text-align: right;"><?php echo esc_html(get_user_meta($teacher->ID, 'sm_phone', true)); ?></td>
                            <td><?php echo esc_html($teacher->user_email); ?></td>
                            <td>
                                <div style="display:flex; gap:8px; justify-content: flex-end;">
                                    <button onclick='editSmTeacher(<?php echo json_encode(array(
                                        "id" => $teacher->ID,
                                        "name" => $teacher->display_name,
                                        "email" => $teacher->user_email,
                                        "login" => $teacher->user_login,
                                        "teacher_id" => get_user_meta($teacher->ID, "sm_teacher_id", true),
                                        "job_title" => get_user_meta($teacher->ID, "sm_job_title", true),
                                        "phone" => get_user_meta($teacher->ID, "sm_phone", true)
                                    )); ?>)' class="sm-btn sm-btn-outline" style="padding: 5px 12px; font-size: 12px;">تعديل</button>
                                    
                                    <form method="post" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المعلم؟ لا يمكن التراجع.')">
                                        <?php wp_nonce_field('sm_teacher_action', 'sm_nonce'); ?>
                                        <input type="hidden" name="delete_teacher_id" value="<?php echo $teacher->ID; ?>">
                                        <button type="submit" name="sm_delete_teacher" class="sm-btn sm-btn-outline" style="padding: 5px 12px; font-size: 12px; color:#e53e3e;">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


    <div id="edit-teacher-modal" class="sm-modal-overlay">
        <div class="sm-modal-content">
            <div class="sm-modal-header">
                <h3>تعديل بيانات المعلم</h3>
                <button class="sm-modal-close" onclick="document.getElementById('edit-teacher-modal').style.display='none'">&times;</button>
            </div>
            <form id="edit-teacher-form">
                <?php wp_nonce_field('sm_teacher_action', 'sm_nonce'); ?>
                <input type="hidden" name="edit_teacher_id" id="edit_t_id">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل:</label>
                        <input type="text" name="display_name" id="edit_t_name" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">كود الموظف (ID):</label>
                        <input type="text" name="teacher_id" id="edit_t_code" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">المسمى الوظيفي:</label>
                        <input type="text" name="job_title" id="edit_t_job" class="sm-input">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">رقم الهاتف:</label>
                        <input type="text" name="phone" id="edit_t_phone" class="sm-input">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">البريد الإلكتروني:</label>
                        <input type="email" name="user_email" id="edit_t_email" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">كلمة مرور جديدة (اختياري):</label>
                        <input type="password" name="user_pass" class="sm-input" placeholder="اتركه فارغاً لعدم التغيير">
                    </div>
                </div>
                <button type="submit" class="sm-btn" style="margin-top:20px;">حفظ التغييرات</button>
            </form>
        </div>
    </div>

    <div id="add-teacher-modal" class="sm-modal-overlay">
        <div class="sm-modal-content">
            <div class="sm-modal-header">
                <h3>إضافة معلم جديد للنظام</h3>
                <button class="sm-modal-close" onclick="document.getElementById('add-teacher-modal').style.display='none'">&times;</button>
            </div>
            <form id="add-teacher-form">
                <?php wp_nonce_field('sm_teacher_action', 'sm_nonce'); ?>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل:</label>
                        <input type="text" name="display_name" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">كود الموظف (ID):</label>
                        <input type="text" name="teacher_id" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">المسمى الوظيفي:</label>
                        <input type="text" name="job_title" class="sm-input" placeholder="مثال: معلم لغة عربية">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">رقم الهاتف:</label>
                        <input type="text" name="phone" class="sm-input">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">اسم المستخدم (Login):</label>
                        <input type="text" name="user_login" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">البريد الإلكتروني:</label>
                        <input type="email" name="user_email" class="sm-input" required>
                    </div>
                    <div class="sm-form-group" style="grid-column: span 2;">
                        <label class="sm-label">كلمة المرور المؤقتة:</label>
                        <input type="password" name="user_pass" class="sm-input" required>
                    </div>
                </div>
                <button type="submit" class="sm-btn" style="margin-top:20px;">إنشاء حساب المعلم</button>
            </form>
        </div>
    </div>

    <script>
    (function() {
        const addForm = document.getElementById('add-teacher-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_add_teacher_ajax');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تمت إضافة المعلم');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        smShowNotification('خطأ: ' + res.data, true);
                    }
                });
            });
        }

        const editForm = document.getElementById('edit-teacher-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_update_teacher_ajax');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تم تحديث بيانات المعلم');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            });
        }
    })();
    </script>
</div>
