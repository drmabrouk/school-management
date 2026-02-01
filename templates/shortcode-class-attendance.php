<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-class-attendance-shortcode" dir="rtl" style="max-width: 800px; margin: 20px auto; padding: 20px; background: #fff; border-radius: var(--sm-radius); border: 1px solid var(--sm-border-color); box-shadow: var(--sm-shadow);">
    <h2 style="text-align: center; font-weight: 800; color: var(--sm-dark-color); margin-bottom: 30px;">تسجيل حضور الحصة</h2>

    <?php $academic = SM_Settings::get_academic_structure(); ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
        <div class="sm-form-group">
            <label class="sm-label">الصف الدراسي:</label>
            <select id="at-grade-select" class="sm-select" onchange="atUpdateSections()">
                <option value="">اختر الصف...</option>
                <?php
                $active_grades = $academic['active_grades'];
                sort($active_grades, SORT_NUMERIC);
                foreach ($active_grades as $grade_num): ?>
                    <option value="الصف <?php echo $grade_num; ?>" data-grade-num="<?php echo $grade_num; ?>">الصف <?php echo $grade_num; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="sm-form-group">
            <label class="sm-label">الشعبة:</label>
            <select id="at-section-select" class="sm-select" disabled onchange="atLoadStudents()">
                <option value="">اختر الشعبة...</option>
            </select>
        </div>
    </div>

    <div id="at-students-container" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px;">
            <div style="font-weight: 700; color: var(--sm-dark-color);">قائمة الطلاب:</div>
            <div style="display: flex; gap: 10px;">
                <button onclick="atSetAll('present')" class="sm-btn" style="background: #38a169; font-size: 11px; padding: 5px 12px;">الكل حاضر</button>
            </div>
        </div>

        <div id="at-students-list" style="margin-bottom: 30px;">
            <!-- Loaded via AJAX -->
        </div>

        <div style="text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
            <button onclick="atSubmitAttendance()" class="sm-btn" style="width: 100%; height: 50px; font-size: 1.1em;">إرسال كشف الحضور للنظام</button>
        </div>
    </div>

    <div id="at-no-selection" style="text-align: center; padding: 50px; color: var(--sm-text-gray);">
        <span class="dashicons dashicons-id-alt" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.3;"></span>
        <p>يرجى اختيار الصف والشعبة لعرض قائمة الطلاب</p>
    </div>
</div>

<script>
const academicStructure = <?php echo json_encode($academic); ?>;
const dbStructure = <?php echo json_encode(SM_Settings::get_sections_from_db()); ?>;

function atUpdateSections() {
    const gradeSelect = document.getElementById('at-grade-select');
    const sectionSelect = document.getElementById('at-section-select');
    const gradeNum = gradeSelect.options[gradeSelect.selectedIndex].getAttribute('data-grade-num');

    sectionSelect.innerHTML = '<option value="">اختر الشعبة...</option>';

    if (!gradeNum) {
        sectionSelect.disabled = true;
        document.getElementById('at-students-container').style.display = 'none';
        document.getElementById('at-no-selection').style.display = 'block';
        return;
    }

    const sections = dbStructure[gradeNum] || [];

    sections.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s;
        opt.innerText = 'شعبة ' + s;
        sectionSelect.appendChild(opt);
    });

    sectionSelect.disabled = false;
    document.getElementById('at-students-container').style.display = 'none';
    document.getElementById('at-no-selection').style.display = 'block';
}

function atLoadStudents() {
    const className = document.getElementById('at-grade-select').value;
    const section = document.getElementById('at-section-select').value;
    const listContainer = document.getElementById('at-students-list');
    const container = document.getElementById('at-students-container');
    const noSel = document.getElementById('at-no-selection');

    if (!className || !section) {
        container.style.display = 'none';
        noSel.style.display = 'block';
        return;
    }

    noSel.style.display = 'none';
    container.style.display = 'block';
    listContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--sm-text-gray);">جاري تحميل الطلاب...</div>';

    const date = new Date().toISOString().split('T')[0];
    const formData = new FormData();
    formData.append('action', 'sm_get_students_attendance_ajax');
    formData.append('class_name', className);
    formData.append('section', section);
    formData.append('date', date);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            atRenderList(res.data);
        } else {
            listContainer.innerHTML = '<div style="color: red; padding: 20px;">' + res.data + '</div>';
        }
    });
}

function atRenderList(students) {
    const listContainer = document.getElementById('at-students-list');
    if (students.length === 0) {
        listContainer.innerHTML = '<div style="padding: 40px; text-align: center;">لا يوجد طلاب مسجلين في هذه الشعبة.</div>';
        return;
    }

    let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
    students.forEach(s => {
        const photo = s.photo_url ? `<img src="${s.photo_url}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">` : `<div style="width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 18px;">👤</div>`;

        // Status class
        let activeStatus = s.status || 'present'; // Default to present for the shortcode UI if not marked

        html += `
            <div class="at-student-row" data-student-id="${s.id}" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    ${photo}
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">${s.name}</div>
                        <div style="font-size: 11px; color: var(--sm-text-gray);">${s.student_code}</div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="atSetStatus(this, 'present')" class="at-choice-btn ${activeStatus === 'present' ? 'active' : ''}" data-status="present" title="حاضر">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </button>
                    <button onclick="atSetStatus(this, 'late')" class="at-choice-btn ${activeStatus === 'late' ? 'active' : ''}" data-status="late" title="متأخر">
                        <span class="dashicons dashicons-clock"></span>
                    </button>
                    <button onclick="atSetStatus(this, 'absent')" class="at-choice-btn ${activeStatus === 'absent' ? 'active' : ''}" data-status="absent" title="غائب">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    listContainer.innerHTML = html;
}

function atSetStatus(btn, status) {
    const row = btn.closest('.at-student-row');
    row.querySelectorAll('.at-choice-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function atSetAll(status) {
    document.querySelectorAll(`.at-choice-btn[data-status="${status}"]`).forEach(btn => btn.click());
}

async function atSubmitAttendance() {
    const rows = document.querySelectorAll('.at-student-row');
    const date = new Date().toISOString().split('T')[0];
    const nonce = '<?php echo wp_create_nonce("sm_attendance_action"); ?>';

    let successCount = 0;
    const btn = event.target;
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = 'جاري الحفظ...';

    for (const row of rows) {
        const studentId = row.getAttribute('data-student-id');
        const activeBtn = row.querySelector('.at-choice-btn.active');
        const status = activeBtn ? activeBtn.getAttribute('data-status') : 'present';

        const formData = new FormData();
        formData.append('action', 'sm_save_attendance_ajax');
        formData.append('student_id', studentId);
        formData.append('status', status);
        formData.append('date', date);
        formData.append('nonce', nonce);

        try {
            const r = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData });
            const res = await r.json();
            if (res.success) successCount++;
        } catch(e) {}
    }

    if (successCount === rows.length) {
        alert('تم حفظ كشف الحضور بنجاح');
        location.reload();
    } else {
        alert('حدث خطأ في حفظ بعض السجلات. تم حفظ ' + successCount + ' من ' + rows.length);
        btn.disabled = false;
        btn.innerText = originalText;
    }
}
</script>

<style>
.at-choice-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
    color: #cbd5e0;
}
.at-choice-btn .dashicons { font-size: 20px; width: 20px; height: 20px; }
.at-choice-btn[data-status="present"].active { background: #38a169; color: #fff; border-color: #38a169; }
.at-choice-btn[data-status="late"].active { background: #ecc94b; color: #fff; border-color: #ecc94b; }
.at-choice-btn[data-status="absent"].active { background: #e53e3e; color: #fff; border-color: #e53e3e; }

.at-student-row:hover { border-color: var(--sm-primary-color); }

@media (max-width: 600px) {
    .at-student-row { flex-direction: column; gap: 15px; align-items: flex-start; }
    .at-student-row > div:last-child { width: 100%; justify-content: flex-end; }
}
</style>
