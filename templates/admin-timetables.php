<?php if (!defined('ABSPATH')) exit;
$db_structure = SM_Settings::get_sections_from_db();
$selected_class = isset($_GET['class_name']) ? sanitize_text_field($_GET['class_name']) : '';
$selected_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : '';
?>

<div class="sm-timetables-container">
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); margin-bottom: 30px;">
        <h3 style="margin-top:0;">إدارة الجداول المدرسية</h3>
        <form method="get" style="display: flex; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="sm_tab" value="timetables">
            <div class="sm-form-group">
                <label class="sm-label">الصف الدراسي:</label>
                <select name="class_name" class="sm-select" onchange="this.form.submit()">
                    <option value="">اختر الصف</option>
                    <?php foreach ($db_structure as $grade => $sects): $cname = 'الصف '.$grade; ?>
                        <option value="<?php echo $cname; ?>" <?php selected($selected_class, $cname); ?>><?php echo $cname; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selected_class):
                $grade_num = preg_replace('/[^0-9]/', '', $selected_class);
                $sections = $db_structure[$grade_num] ?? array();
            ?>
            <div class="sm-form-group">
                <label class="sm-label">الشعبة:</label>
                <select name="section" class="sm-select" onchange="this.form.submit()">
                    <option value="">اختر الشعبة</option>
                    <?php foreach ($sections as $s): ?>
                        <option value="<?php echo $s; ?>" <?php selected($selected_section, $s); ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($selected_class && $selected_section):
        $timetable = SM_DB::get_timetable($selected_class, $selected_section);
        $grid = array();
        foreach ($timetable as $t) {
            $grid[$t->day][$t->period] = $t;
        }
        $days = array(
            'sun' => 'الأحد',
            'mon' => 'الاثنين',
            'tue' => 'الثلاثاء',
            'wed' => 'الأربعاء',
            'thu' => 'الخميس'
        );
        $periods = 8;
        $subjects = SM_DB::get_subjects(); // Get all subjects for dropdown
        $teachers = get_users(array('role' => 'sm_teacher'));
    ?>
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
        <h4 style="margin-top:0; color: var(--sm-primary-color);">الجدول الدراسي لـ: <?php echo $selected_class . ' ' . $selected_section; ?></h4>
        <div class="sm-table-container">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>اليوم / الحصة</th>
                        <?php for($i=1; $i<=$periods; $i++) echo "<th>الحصة $i</th>"; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day_key => $day_label): ?>
                    <tr>
                        <td style="font-weight: 800; background: #f8fafc;"><?php echo $day_label; ?></td>
                        <?php for($i=1; $i<=$periods; $i++):
                            $entry = $grid[$day_key][$i] ?? null;
                        ?>
                        <td style="padding: 10px; min-width: 150px;">
                            <div class="sm-timetable-cell" onclick="smEditTimetable('<?php echo $day_key; ?>', <?php echo $i; ?>, '<?php echo $day_label; ?>', <?php echo $entry ? $entry->subject_id : 0; ?>, <?php echo $entry ? $entry->teacher_id : 0; ?>)" style="cursor: pointer; padding: 10px; border: 1px dashed #cbd5e0; border-radius: 6px; min-height: 60px;">
                                <?php if ($entry): ?>
                                    <div style="font-weight: 800; font-size: 13px; color: var(--sm-dark-color);"><?php echo esc_html($entry->subject_name); ?></div>
                                    <div style="font-size: 11px; color: #718096; margin-top: 5px;"><?php echo esc_html($entry->teacher_name); ?></div>
                                <?php else: ?>
                                    <div style="color: #a0aec0; font-size: 11px; text-align: center;">إضافة</div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endfor; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- EDIT TIMETABLE MODAL -->
<div id="edit-timetable-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 450px;">
        <div class="sm-modal-header">
            <h3>تعديل الحصة الدراسية</h3>
            <button class="sm-modal-close" onclick="this.closest('.sm-modal-overlay').style.display='none'">&times;</button>
        </div>
        <div class="sm-modal-body">
            <p id="timetable-info" style="font-weight: 700; margin-bottom: 20px; color: var(--sm-primary-color);"></p>
            <input type="hidden" id="tt_day">
            <input type="hidden" id="tt_period">

            <div class="sm-form-group">
                <label class="sm-label">المادة الدراسية:</label>
                <select id="tt_subject" class="sm-select">
                    <option value="">-- اختر المادة --</option>
                    <?php foreach ($subjects as $sub) echo "<option value='{$sub->id}'>{$sub->name}</option>"; ?>
                </select>
            </div>
            <div class="sm-form-group">
                <label class="sm-label">المعلم:</label>
                <select id="tt_teacher" class="sm-select">
                    <option value="">-- اختر المعلم --</option>
                    <?php foreach ($teachers as $t) echo "<option value='{$t->ID}'>{$t->display_name}</option>"; ?>
                </select>
            </div>
            <button class="sm-btn" onclick="smSaveTimetableEntry()">حفظ التعديل</button>
        </div>
    </div>
</div>

<script>
function smEditTimetable(dayKey, period, dayLabel, subjectId, teacherId) {
    document.getElementById('tt_day').value = dayKey;
    document.getElementById('tt_period').value = period;
    document.getElementById('timetable-info').innerText = dayLabel + ' - الحصة ' + period;
    document.getElementById('tt_subject').value = subjectId || '';
    document.getElementById('tt_teacher').value = teacherId || '';
    document.getElementById('edit-timetable-modal').style.display = 'flex';
}

function smSaveTimetableEntry() {
    const day = document.getElementById('tt_day').value;
    const period = document.getElementById('tt_period').value;
    const subjectId = document.getElementById('tt_subject').value;
    const teacherId = document.getElementById('tt_teacher').value;

    if (!subjectId || !teacherId) {
        smShowNotification('يرجى اختيار المادة والمعلم', true);
        return;
    }

    const formData = new FormData();
    formData.append('action', 'sm_update_timetable_entry');
    formData.append('class_name', '<?php echo $selected_class; ?>');
    formData.append('section', '<?php echo $selected_section; ?>');
    formData.append('day', day);
    formData.append('period', period);
    formData.append('subject_id', subjectId);
    formData.append('teacher_id', teacherId);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم تحديث الجدول بنجاح');
            location.reload();
        } else {
            smShowNotification('فشل التحديث', true);
        }
    });
}
</script>
