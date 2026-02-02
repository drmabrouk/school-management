<?php
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
$roles = (array) $user->roles;
$can_manage = current_user_can('manage_grades') || current_user_can('manage_options');

if (!$can_manage) {
    echo '<p>غير مسموح لك بالوصول لهذه الصفحة.</p>';
    return;
}

$students = SM_DB::get_students();
?>

<div class="sm-grades-management" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 style="margin: 0; font-weight: 800;">إدارة الدرجات والنتائج الأكاديمية</h3>
    </div>

    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); margin-bottom: 30px;">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">اختر الطالب:</label>
                <select id="grade-student-id" class="sm-select">
                    <option value="">-- اختر طالب --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->name); ?> (<?php echo $s->class_name; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">المادة:</label>
                <input type="text" id="grade-subject" class="sm-input" placeholder="مثال: لغة عربية">
            </div>
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">الفصل:</label>
                <select id="grade-term" class="sm-select">
                    <option value="الفصل الأول">الفصل الأول</option>
                    <option value="الفصل الثاني">الفصل الثاني</option>
                    <option value="الفصل الثالث">الفصل الثالث</option>
                </select>
            </div>
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">الدرجة:</label>
                <input type="text" id="grade-val" class="sm-input" placeholder="100/95">
            </div>
            <button onclick="saveStudentGrade()" class="sm-btn" style="height: 45px; background: var(--sm-primary-color);">رصد الدرجة</button>
        </div>
    </div>

    <div id="grades-table-container">
        <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; color: var(--sm-text-gray);">يرجى اختيار طالب لعرض درجاته.</div>
    </div>
</div>

<script>
document.getElementById('grade-student-id').addEventListener('change', function() {
    loadStudentGrades(this.value);
});

function loadStudentGrades(studentId) {
    if (!studentId) {
        document.getElementById('grades-table-container').innerHTML = '<div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; color: var(--sm-text-gray);">يرجى اختيار طالب لعرض درجاته.</div>';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'sm_get_student_grades_ajax');
    formData.append('student_id', studentId);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            renderGradesTable(res.data);
        }
    });
}

function renderGradesTable(grades) {
    const container = document.getElementById('grades-table-container');
    if (grades.length === 0) {
        container.innerHTML = '<div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; color: var(--sm-text-gray);">لا يوجد درجات مسجلة لهذا الطالب.</div>';
        return;
    }

    let html = `
        <div class="sm-table-container">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>المادة</th>
                        <th>الفصل</th>
                        <th>الدرجة</th>
                        <th>تاريخ الرصد</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
    `;

    grades.forEach(g => {
        html += `
            <tr>
                <td style="font-weight:700;">${g.subject}</td>
                <td>${g.term}</td>
                <td><span class="sm-badge" style="background:var(--sm-bg-light); color:var(--sm-primary-color); font-size:1.1em;">${g.grade_val}</span></td>
                <td>${g.created_at}</td>
                <td>
                    <button onclick="deleteGrade(${g.id})" class="sm-btn sm-btn-outline" style="color:red; padding:5px;"><span class="dashicons dashicons-trash"></span></button>
                </td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function saveStudentGrade() {
    const studentId = document.getElementById('grade-student-id').value;
    const subject = document.getElementById('grade-subject').value;
    const term = document.getElementById('grade-term').value;
    const gradeVal = document.getElementById('grade-val').value;

    if (!studentId || !subject || !gradeVal) {
        alert('يرجى إكمال كافة الحقول');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'sm_save_grade_ajax');
    formData.append('student_id', studentId);
    formData.append('subject', subject);
    formData.append('term', term);
    formData.append('grade_val', gradeVal);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_grade_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم رصد الدرجة بنجاح');
            loadStudentGrades(studentId);
            document.getElementById('grade-subject').value = '';
            document.getElementById('grade-val').value = '';
        }
    });
}

function deleteGrade(gradeId) {
    if (!confirm('هل أنت متأكد من حذف هذه الدرجة؟')) return;

    const formData = new FormData();
    formData.append('action', 'sm_delete_grade_ajax');
    formData.append('grade_id', gradeId);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_grade_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم حذف الدرجة');
            loadStudentGrades(document.getElementById('grade-student-id').value);
        }
    });
}
</script>
