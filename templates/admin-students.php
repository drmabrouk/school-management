<?php if (!defined('ABSPATH')) exit; ?>
<?php $is_admin = current_user_can('إدارة_الطلاب'); ?>
<div class="sm-content-wrapper" dir="rtl">
    <div style="background: white; padding: 30px; border: 1px solid var(--sm-border-color); border-radius: var(--sm-radius); margin-bottom: 30px; box-shadow: var(--sm-shadow);">
        <form method="get" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 20px; align-items: end;">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">بحث سريع عن طالب:</label>
                <input type="text" name="student_search" class="sm-input" value="<?php echo esc_attr(isset($_GET['student_search']) ? $_GET['student_search'] : ''); ?>" placeholder="الاسم، الكود، أو الصف...">
            </div>
            
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">تصفية حسب الصف الدراسي:</label>
                <select name="class_filter" class="sm-select">
                    <option value="">كل الصفوف الدراسية</option>
                    <?php 
                    global $wpdb;
                    $classes = $wpdb->get_col("SELECT DISTINCT class_name FROM {$wpdb->prefix}sm_students");
                    foreach ($classes as $c): ?>
                        <option value="<?php echo esc_attr($c); ?>" <?php selected(isset($_GET['class_filter']) && $_GET['class_filter'] == $c); ?>><?php echo esc_html($c); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="sm-btn">تطبيق التصفية</button>
                <a href="?page=<?php echo esc_attr($_GET['page']); ?>" class="sm-btn sm-btn-outline" style="text-decoration:none;">إعادة ضبط</a>
            </div>
        </form>
    </div>

    <?php if ($is_admin): ?>
    <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; align-items: center;">
        <button onclick="document.getElementById('add-single-student-modal').style.display='flex'" class="sm-btn">+ إضافة طالب جديد</button>
        <button onclick="document.getElementById('csv-import-form').style.display='block'" class="sm-btn sm-btn-secondary">استيراد طلاب (Excel)</button>
        <a href="data:text/csv;charset=utf-8,<?php echo rawurlencode("الاسم,الصف,البريد,الكود\nأحمد محمد,الصف الأول,parent@example.com,STU100"); ?>" download="student_template.csv" class="sm-btn sm-btn-outline" style="text-decoration:none;">تحميل نموذج CSV</a>
        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=id_card'); ?>" target="_blank" class="sm-btn sm-btn-accent" style="background: #27ae60; text-decoration:none;">طباعة كافة البطاقات</a>
    </div>
    <?php endif; ?>

    <div id="csv-import-form" style="display:none; background: #f8fafc; padding: 30px; border: 2px dashed #cbd5e0; border-radius: 12px; margin-bottom: 30px;">
        <h3 style="margin-top:0; color:var(--sm-secondary-color);">دليل استيراد الطلاب (Excel/CSV)</h3>
        
        <div style="background:#fff; padding:15px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
            <p style="font-size:13px; font-weight:700; margin-bottom:10px;">هيكل ملف الاستيراد الصحيح:</p>
            <table style="width:100%; font-size:12px; border-collapse:collapse; text-align:center;">
                <thead>
                    <tr style="background:#edf2f7;">
                        <th style="border:1px solid #cbd5e0; padding:5px;">الاسم الكامل</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الصف الدراسي</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">بريد ولي الأمر</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الكود (اختياري)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border:1px solid #cbd5e0; padding:5px;">محمد علي</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">الصف الأول</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">parent@mail.com</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">STU101</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('sm_admin_action', 'sm_admin_nonce'); ?>
            <div class="sm-form-group">
                <label class="sm-label">اختر ملف CSV للملفات:</label>
                <input type="file" name="csv_file" accept=".csv" required>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="sm_import_csv" class="sm-btn" style="width:auto; background:#27ae60;">بدء عملية الاستيراد</button>
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="sm-btn" style="width:auto; background:var(--sm-text-gray);">إلغاء</button>
            </div>
        </form>
    </div>
    
    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>اسم الطالب</th>
                    <th>الصف الدراسي</th>
                    <th>كود الطالب</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" style="padding: 60px; text-align: center; color: var(--sm-text-gray);">
                            <span class="dashicons dashicons-search" style="font-size: 40px; width:40px; height:40px; margin-bottom:10px;"></span>
                            <p>لا يوجد طلاب يطابقون معايير البحث الحالية.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr id="stu-row-<?php echo $student->id; ?>">
                            <td>
                                <?php if ($student->photo_url): ?>
                                    <img src="<?php echo esc_url($student->photo_url); ?>" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--sm-border-color);">
                                <?php else: ?>
                                    <div style="width: 45px; height: 45px; border-radius: 50%; background: var(--sm-bg-light); display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--sm-text-gray);">👤</div>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 800; color: var(--sm-dark-color);"><?php echo esc_html($student->name); ?></td>
                            <td><span class="sm-badge sm-badge-low"><?php echo esc_html($student->class_name); ?></span></td>
                            <td style="font-family: monospace; font-weight: 700; color: var(--sm-primary-color);"><?php echo esc_html($student->student_code); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button onclick='viewSmStudent(<?php echo json_encode(array(
                                        "id" => $student->id,
                                        "name" => $student->name,
                                        "student_id" => $student->student_code,
                                        "class" => $student->class_name,
                                        "photo" => $student->photo_url
                                    )); ?>)' class="sm-btn sm-btn-outline" style="padding: 5px 12px; font-size: 12px;">
                                        <span class="dashicons dashicons-visibility"></span> عرض السجل
                                    </button>

                                    <?php if ($is_admin): ?>
                                        <button onclick='editSmStudent(<?php echo json_encode(array(
                                            "id" => $student->id,
                                            "name" => $student->name,
                                            "student_id" => $student->student_code,
                                            "class" => $student->class_name,
                                            "parent_id" => $student->parent_user_id,
                                            "parent_email" => $student->parent_email,
                                            "teacher_id" => $student->teacher_id,
                                            "photo" => $student->photo_url
                                        )); ?>)' class="sm-btn sm-btn-outline" style="padding: 5px; min-width: 32px;" title="تعديل"><span class="dashicons dashicons-edit"></span></button>

                                        <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=id_card&student_id=' . $student->id); ?>" target="_blank" class="sm-btn sm-btn-outline" style="padding: 5px; min-width: 32px;" title="بطاقة الهوية"><span class="dashicons dashicons-id"></span></a>

                                        <button onclick="confirmDeleteStudent(<?php echo $student->id; ?>, '<?php echo esc_js($student->name); ?>')" class="sm-btn sm-btn-outline" style="padding: 5px; min-width: 32px; color: #e53e3e;" title="حذف الطالب"><span class="dashicons dashicons-trash"></span></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <style>
    .sm-student-row:hover {
        border-color: var(--sm-primary-color);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        transform: translateX(-5px);
    }
    .sm-action-btn-row {
        padding: 8px 15px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .sm-action-btn-row:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }
    .sm-icon-btn-row {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #4a5568;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .sm-icon-btn-row:hover {
        background: #edf2f7;
        color: var(--sm-primary-color);
    }
    </style>

    <?php if ($is_admin): ?>
    <div id="add-single-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 750px;">
            <div class="sm-modal-header">
                <h3>تسجيل طالب جديد في النظام</h3>
                <button class="sm-modal-close" onclick="document.getElementById('add-single-student-modal').style.display='none'">&times;</button>
            </div>
            <form id="add-student-form">
                <?php wp_nonce_field('sm_add_student', 'sm_nonce'); ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background:#f8fafc; padding:25px; border-radius:12px; border:1px solid #edf2f7;">
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الثلاثي للطالب:</label>
                        <input name="name" type="text" class="sm-input" placeholder="أدخل الاسم كاملاً..." required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الصف الدراسي:</label>
                        <select name="class" class="sm-select" required>
                            <option value="">-- اختر الصف --</option>
                            <?php 
                            $academic = SM_Settings::get_academic_structure();
                            $grades = explode(',', $academic['academic_stages']);
                            for($i=1; $i<=$academic['grades_count']; $i++) echo "<option value='الصف $i'>الصف $i</option>";
                            ?>
                        </select>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">بريد ولي الأمر:</label>
                        <input name="email" type="email" class="sm-input" placeholder="example@mail.com" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">رقم القيد / الكود:</label>
                        <input name="code" type="text" value="<?php echo 'STU' . rand(1000, 9999); ?>" class="sm-input" style="font-family:monospace; font-weight:700; color:var(--sm-primary-color);">
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">ربط بحساب ولي أمر (اختياري):</label>
                        <select name="parent_user_id" class="sm-select">
                            <option value="">-- بلا ربط --</option>
                            <?php foreach (get_users(array('role' => 'sm_parent')) as $p): ?>
                                <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">المعلم المسؤول:</label>
                        <select name="teacher_id" class="sm-select">
                            <option value="">-- بلا ربط --</option>
                            <?php foreach (get_users(array('role' => 'sm_teacher')) as $t): ?>
                                <option value="<?php echo $t->ID; ?>"><?php echo esc_html($t->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="text-align:left; margin-top:25px;">
                    <button type="submit" class="sm-btn" style="width:220px; height:50px; font-weight:800; font-size:1.05em;">تأكيد إضافة الطالب</button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 800px;">
            <div class="sm-modal-header">
                <h3>تعديل الملف المعلوماتي للطالب</h3>
                <button class="sm-modal-close" onclick="document.getElementById('edit-student-modal').style.display='none'">&times;</button>
            </div>
            <form id="edit-student-form">
                <?php wp_nonce_field('sm_add_student', 'sm_nonce'); ?>
                <input type="hidden" name="student_id" id="edit_stu_id">
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; background:#f8fafc; padding:25px; border-radius:12px; border:1px solid #edf2f7;">
                    <div style="grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 5px; color: var(--sm-primary-color); font-weight: 700;">البيانات الأساسية</div>
                    
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل للطالب:</label>
                        <input type="text" name="name" id="edit_stu_name" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الصف الدراسي / المستوى:</label>
                        <input type="text" name="class_name" id="edit_stu_class" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">الرقم الأكاديمي (الكود):</label>
                        <input type="text" name="student_code" id="edit_stu_code" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">بريد التواصل (ولي الأمر):</label>
                        <input type="email" name="parent_email" id="edit_stu_email" class="sm-input" required>
                    </div>

                    <div style="grid-column: span 2; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-top: 15px; margin-bottom: 5px; color: var(--sm-primary-color); font-weight: 700;">الربط والمتابعة</div>

                    <div class="sm-form-group">
                        <label class="sm-label">ربط بحساب ولي أمر مسجل:</label>
                        <select name="parent_user_id" id="edit_stu_parent_user" class="sm-select">
                            <option value="">-- اختر من مستخدمي النظام --</option>
                            <?php foreach (get_users(array('role' => 'sm_parent')) as $p): ?>
                                <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">المعلم المشرف / المسؤول:</label>
                        <select name="teacher_id" id="edit_stu_teacher" class="sm-select">
                            <option value="">-- اختر من المعلمين --</option>
                            <?php foreach (get_users(array('role' => 'sm_teacher')) as $t): ?>
                                <option value="<?php echo $t->ID; ?>"><?php echo esc_html($t->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:15px; margin-top:30px; justify-content: flex-end;">
                    <button type="submit" class="sm-btn" style="width:200px; height:50px; font-weight:800;">تحديث البيانات الآن</button>
                    <button type="button" onclick="document.getElementById('edit-student-modal').style.display='none'" class="sm-btn" style="background:#cbd5e0; color:#2d3748; width:120px;">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div id="delete-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 400px; text-align: center;">
            <div class="sm-modal-header">
                <h3>تأكيد الحذف</h3>
                <button class="sm-modal-close" onclick="document.getElementById('delete-student-modal').style.display='none'">&times;</button>
            </div>
            <div style="color: #e53e3e; font-size: 50px; margin-bottom: 15px;"><span class="dashicons dashicons-warning" style="font-size: 50px; width:50px; height:50px;"></span></div>
            <p id="delete-confirm-msg">هل أنت متأكد من حذف الطالب وسجلاته بالكامل؟</p>
            <form method="post" id="delete-student-form">
                <?php wp_nonce_field('sm_add_student', 'sm_nonce'); ?>
                <input type="hidden" name="delete_student_id" id="confirm_delete_stu_id">
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit" name="delete_student" class="sm-btn" style="background: #e53e3e;">تأكيد الحذف النهائي</button>
                    <button type="button" onclick="document.getElementById('delete-student-modal').style.display='none'" class="sm-btn" style="background: #cbd5e0; color: #2d3748;">تراجع</button>
                </div>
            </form>
        </div>
    </div>

    <div id="view-student-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 800px;">
            <div class="sm-modal-header">
                <h3>الملف الانضباطي للطالب</h3>
                <button class="sm-modal-close" onclick="document.getElementById('view-student-modal').style.display='none'">&times;</button>
            </div>
            <div id="stu_details_content"></div>
            <div style="margin-top: 30px; text-align: left;">
                <button type="button" onclick="document.getElementById('view-student-modal').style.display='none'" class="sm-btn" style="width:auto; background:var(--sm-text-gray);">إغلاق الملف</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // Handle View Record
        window.viewSmStudent = function(student) {
            const modal = document.getElementById('view-student-modal');
            const content = document.getElementById('stu_details_content');
            if (!modal || !content) return;
            
            content.innerHTML = '<div style="text-align:center; padding:50px;"><p>جاري تحميل السجل...</p></div>';
            modal.style.display = 'flex';

            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=sm_print&print_type=disciplinary_report&student_id=' + student.id)
                .then(r => r.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    content.innerHTML = doc.body.innerHTML;
                    content.querySelectorAll('.no-print').forEach(el => el.remove());
                });
        };

        // Handle Add Student AJAX
        const addForm = document.getElementById('add-student-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_add_student_ajax');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تمت إضافة الطالب بنجاح');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            });
        }

        // Handle Edit Student AJAX
        const editForm = document.getElementById('edit-student-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_update_student_ajax');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تم تحديث بيانات الطالب');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            });
        }

        // Handle Delete
        window.confirmDeleteStudent = function(id, name) {
            document.getElementById('confirm_delete_stu_id').value = id;
            document.getElementById('delete-confirm-msg').innerText = `هل أنت متأكد من حذف الطالب "${name}" وكافة سجلاته؟`;
            document.getElementById('delete-student-modal').style.display = 'flex';
        };

        const deleteForm = document.getElementById('delete-student-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_delete_student_ajax');
                formData.append('nonce', '<?php echo wp_create_nonce("sm_delete_student"); ?>');
                formData.append('student_id', document.getElementById('confirm_delete_stu_id').value);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تم حذف الطالب');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            });
        }
    })();
    </script>
</div>
