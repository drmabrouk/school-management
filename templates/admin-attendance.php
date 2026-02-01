<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-attendance-page" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 style="margin: 0; font-weight: 800;">سجل الحضور والغياب</h3>
        <div style="display: flex; gap: 15px; align-items: center;">
            <div class="sm-form-group" style="margin-bottom: 0;">
                <input type="date" id="attendance-filter-date" class="sm-input" value="<?php echo esc_attr($attendance_date); ?>" onchange="window.location.href='<?php echo add_query_arg('attendance_date', '', $_SERVER['REQUEST_URI']); ?>' + this.value">
            </div>
            <button onclick="location.reload()" class="sm-btn sm-btn-outline" title="تحديث"><span class="dashicons dashicons-update"></span></button>
        </div>
    </div>

    <!-- Stats Summary -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px;">
        <?php
        $total_students = 0;
        $total_present = 0;
        $total_absent = 0;
        $total_late = 0;
        foreach ($attendance_summary as $card) {
            $total_students += $card['student_count'];
            $total_present += $card['stats']['present'];
            $total_absent += $card['stats']['absent'];
            $total_late += $card['stats']['late'];
        }
        ?>
        <div class="sm-stat-card" style="padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0;">
            <div style="font-size: 11px; color: var(--sm-text-gray); font-weight: 700;">إجمالي الطلاب</div>
            <div style="font-size: 1.8em; font-weight: 900; color: var(--sm-dark-color);"><?php echo $total_students; ?></div>
        </div>
        <div class="sm-stat-card" style="padding: 15px; border: 1px solid #c6f6d5; background: #f0fff4;">
            <div style="font-size: 11px; color: #2f855a; font-weight: 700;">حضور</div>
            <div style="font-size: 1.8em; font-weight: 900; color: #38a169;"><?php echo $total_present; ?></div>
        </div>
        <div class="sm-stat-card" style="padding: 15px; border: 1px solid #fed7d7; background: #fff5f5;">
            <div style="font-size: 11px; color: #c53030; font-weight: 700;">غياب</div>
            <div style="font-size: 1.8em; font-weight: 900; color: #e53e3e;"><?php echo $total_absent; ?></div>
        </div>
        <div class="sm-stat-card" style="padding: 15px; border: 1px solid #feebc8; background: #fffaf0;">
            <div style="font-size: 11px; color: #c05621; font-weight: 700;">تأخير</div>
            <div style="font-size: 1.8em; font-weight: 900; color: #ecc94b;"><?php echo $total_late; ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid var(--sm-border-color); margin-bottom: 30px; display: flex; gap: 15px;">
        <input type="text" id="card-search" class="sm-input" placeholder="بحث عن صف أو شعبة..." onkeyup="filterAttendanceCards()">
        <select id="card-status-filter" class="sm-select" onchange="filterAttendanceCards()" style="width: 200px;">
            <option value="all">كل الحالات</option>
            <option value="complete">مكتمل</option>
            <option value="incomplete">غير مكتمل</option>
            <option value="absences">يوجد غيابات</option>
        </select>
    </div>

    <!-- Cards Grid -->
    <div id="attendance-cards-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px;">
        <?php foreach ($attendance_summary as $card):
            $status_color = '#e53e3e'; // Red (Default Incomplete)
            $status_text = 'غير مكتمل';

            if ($card['is_complete']) {
                if ($card['has_absences']) {
                    $status_color = '#ecc94b'; // Yellow (Complete but with absences)
                    $status_text = 'يوجد غيابات/تأخير';
                } else {
                    $status_color = '#38a169'; // Green (Full attendance)
                    $status_text = 'حضور كامل';
                }
            }
        ?>
        <div class="sm-attendance-card"
             data-grade="<?php echo esc_attr($card['class_name']); ?>"
             data-section="<?php echo esc_attr($card['section']); ?>"
             data-complete="<?php echo $card['is_complete'] ? 'yes' : 'no'; ?>"
             data-absences="<?php echo $card['has_absences'] ? 'yes' : 'no'; ?>"
             onclick="openAttendanceModal('<?php echo esc_js($card['class_name']); ?>', '<?php echo esc_js($card['section']); ?>')"
             style="background: #fff; border: 1px solid var(--sm-border-color); border-radius: 12px; padding: 20px; cursor: pointer; transition: 0.2s; position: relative; border-top: 5px solid <?php echo $status_color; ?>; box-shadow: var(--sm-shadow);">

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                <div>
                    <h4 style="margin: 0; font-weight: 800; color: var(--sm-dark-color);"><?php echo esc_html($card['class_name']); ?></h4>
                    <div style="font-size: 14px; color: var(--sm-text-gray); font-weight: 700;">شعبة <?php echo esc_html($card['section']); ?></div>
                </div>
                <div style="background: var(--sm-bg-light); padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                    <?php echo $card['student_count']; ?> طالب
                </div>
            </div>

            <div style="font-size: 12px; color: <?php echo $status_color; ?>; font-weight: 700; display: flex; align-items: center; gap: 5px; margin-bottom: 15px;">
                <span class="dashicons dashicons-marker" style="font-size: 14px; width: 14px; height: 14px;"></span>
                <?php echo $status_text; ?>
            </div>

            <div style="display: flex; gap: 5px; font-size: 10px; font-weight: 700;">
                <span style="background: #f0fff4; color: #38a169; padding: 2px 6px; border-radius: 4px;">ح: <?php echo $card['stats']['present']; ?></span>
                <span style="background: #fff5f5; color: #e53e3e; padding: 2px 6px; border-radius: 4px;">غ: <?php echo $card['stats']['absent']; ?></span>
                <span style="background: #fffff0; color: #ecc94b; padding: 2px 6px; border-radius: 4px;">ت: <?php echo $card['stats']['late']; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Attendance Marking Modal -->
<div id="sm-attendance-marking-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 700px;">
        <div class="sm-modal-header">
            <div>
                <h3 id="modal-attendance-title" style="margin: 0;">تسجيل الحضور</h3>
                <div id="modal-attendance-subtitle" style="font-size: 13px; color: var(--sm-text-gray); margin-top: 5px;"></div>
            </div>
            <button class="sm-modal-close" onclick="closeAttendanceModal()">&times;</button>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #f8fafc; padding: 10px; border-radius: 8px;">
            <div style="font-weight: 700; font-size: 13px;">تغيير سريع للكل:</div>
            <div style="display: flex; gap: 8px;">
                <button onclick="setAllAttendance('present')" class="sm-btn" style="background: #38a169; font-size: 11px; padding: 5px 12px;">حضور للكل</button>
                <button onclick="setAllAttendance('absent')" class="sm-btn" style="background: #e53e3e; font-size: 11px; padding: 5px 12px;">غياب للكل</button>
            </div>
        </div>

        <div id="attendance-students-list" style="max-height: 400px; overflow-y: auto;">
            <!-- Loaded via AJAX -->
            <div style="text-align: center; padding: 40px; color: var(--sm-text-gray);">جاري تحميل قائمة الطلاب...</div>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--sm-border-color); display: flex; justify-content: flex-end;">
            <button onclick="closeAttendanceModal()" class="sm-btn" style="background: var(--sm-dark-color);">إغلاق وحفظ</button>
        </div>
    </div>
</div>

<script>
function filterAttendanceCards() {
    const search = document.getElementById('card-search').value.toLowerCase();
    const status = document.getElementById('card-status-filter').value;
    const cards = document.querySelectorAll('.sm-attendance-card');

    cards.forEach(card => {
        const grade = card.getAttribute('data-grade').toLowerCase();
        const section = card.getAttribute('data-section').toLowerCase();
        const isComplete = card.getAttribute('data-complete') === 'yes';
        const hasAbsences = card.getAttribute('data-absences') === 'yes';

        let show = true;
        if (search && !grade.includes(search) && !section.includes(search)) show = false;

        if (status === 'complete' && !isComplete) show = false;
        if (status === 'incomplete' && isComplete) show = false;
        if (status === 'absences' && !hasAbsences) show = false;

        card.style.display = show ? 'block' : 'none';
    });
}

function openAttendanceModal(className, section) {
    const date = document.getElementById('attendance-filter-date').value;
    document.getElementById('modal-attendance-title').innerText = 'تسجيل حضور: ' + className;
    document.getElementById('modal-attendance-subtitle').innerText = 'الشعبة: ' + section + ' | التاريخ: ' + date;
    document.getElementById('sm-attendance-marking-modal').style.display = 'flex';

    loadAttendanceStudents(className, section, date);
}

function closeAttendanceModal() {
    document.getElementById('sm-attendance-marking-modal').style.display = 'none';
    // Optionally reload to update cards
    location.reload();
}

function loadAttendanceStudents(className, section, date) {
    const listContainer = document.getElementById('attendance-students-list');
    listContainer.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--sm-text-gray);">جاري التحميل...</div>';

    const formData = new FormData();
    formData.append('action', 'sm_get_students_attendance_ajax');
    formData.append('class_name', className);
    formData.append('section', section);
    formData.append('date', date);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            renderStudentsList(res.data);
        } else {
            listContainer.innerHTML = '<div style="color: red; padding: 20px;">' + res.data + '</div>';
        }
    });
}

function renderStudentsList(students) {
    const listContainer = document.getElementById('attendance-students-list');
    if (students.length === 0) {
        listContainer.innerHTML = '<div style="padding: 20px; text-align: center;">لا يوجد طلاب في هذا الصف.</div>';
        return;
    }

    let html = '<table class="sm-table" style="box-shadow: none; border: none;"><tbody>';
    students.forEach(s => {
        const photo = s.photo_url ? `<img src="${s.photo_url}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">` : `<div style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 14px;">👤</div>`;

        html += `
            <tr data-student-id="${s.id}">
                <td style="width: 50px;">${photo}</td>
                <td>
                    <div style="font-weight: 700; font-size: 13px;">${s.name}</div>
                    <div style="font-size: 11px; color: var(--sm-text-gray);">${s.student_code}</div>
                </td>
                <td style="text-align: left;">
                    <div class="attendance-options" style="display: flex; gap: 5px; justify-content: flex-end;">
                        <button onclick="saveAttendance(${s.id}, 'present', this)" class="attendance-btn ${s.status === 'present' ? 'active' : ''}" data-status="present" title="حضور">ح</button>
                        <button onclick="saveAttendance(${s.id}, 'absent', this)" class="attendance-btn ${s.status === 'absent' ? 'active' : ''}" data-status="absent" title="غياب">غ</button>
                        <button onclick="saveAttendance(${s.id}, 'late', this)" class="attendance-btn ${s.status === 'late' ? 'active' : ''}" data-status="late" title="تأخير">ت</button>
                        <button onclick="saveAttendance(${s.id}, 'excused', this)" class="attendance-btn ${s.status === 'excused' ? 'active' : ''}" data-status="excused" title="بعذر">ع</button>
                    </div>
                </td>
            </tr>
        `;
    });
    html += '</tbody></table>';
    listContainer.innerHTML = html;
}

function saveAttendance(studentId, status, btn) {
    const date = document.getElementById('attendance-filter-date').value;
    const row = btn.closest('tr');

    // UI Update
    row.querySelectorAll('.attendance-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const formData = new FormData();
    formData.append('action', 'sm_save_attendance_ajax');
    formData.append('student_id', studentId);
    formData.append('status', status);
    formData.append('date', date);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_attendance_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            smShowNotification('خطأ في حفظ الحضور: ' + res.data, true);
            btn.classList.remove('active');
        }
    });
}

function setAllAttendance(status) {
    const buttons = document.querySelectorAll(`.attendance-btn[data-status="${status}"]`);
    buttons.forEach(btn => btn.click());
}
</script>

<style>
#attendance-cards-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 25px;
}

@media (max-width: 1400px) {
    #attendance-cards-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 1100px) {
    #attendance-cards-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) {
    #attendance-cards-grid { grid-template-columns: 1fr; }
}

.sm-attendance-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}
.attendance-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    cursor: pointer;
    font-weight: 800;
    font-size: 12px;
    transition: 0.2s;
    color: var(--sm-text-gray);
}
.attendance-btn[data-status="present"]:hover, .attendance-btn[data-status="present"].active { background: #38a169; color: #fff; border-color: #38a169; }
.attendance-btn[data-status="absent"]:hover, .attendance-btn[data-status="absent"].active { background: #e53e3e; color: #fff; border-color: #e53e3e; }
.attendance-btn[data-status="late"]:hover, .attendance-btn[data-status="late"].active { background: #ecc94b; color: #fff; border-color: #ecc94b; }
.attendance-btn[data-status="excused"]:hover, .attendance-btn[data-status="excused"].active { background: #4299e1; color: #fff; border-color: #4299e1; }
</style>
