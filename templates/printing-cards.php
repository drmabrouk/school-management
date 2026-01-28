<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-printing-center" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 style="margin:0; border:none; padding:0;">مركز الطباعة والتقارير</h3>
        <div style="background: #f0f7ff; padding: 10px 20px; border-radius: 8px; border: 1px solid #c3dafe; font-size: 0.9em; color: var(--sm-primary-color); font-weight: 600;">
            إعدادات الطباعة: A4 عمودي
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
        <!-- Student ID Cards -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; color:var(--sm-secondary-color); display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-id-alt"></span> طباعة بطاقات الهوية
            </h4>
            <p style="font-size: 0.85em; color: #666; margin-bottom: 20px;">توليد بطاقات تعريفية للطلاب تحتوي على الكود والباركود للاتخدام مع ماسح النظام.</p>
            <div class="sm-form-group">
                <label class="sm-label" style="font-size: 12px;">تحديد الصف (اختياري):</label>
                <select id="card_class_filter" class="sm-select">
                    <option value="">كل الصفوف</option>
                    <?php 
                    global $wpdb;
                    $classes = $wpdb->get_col("SELECT DISTINCT class_name FROM {$wpdb->prefix}sm_students");
                    foreach($classes as $c) echo '<option value="'.$c.'">'.$c.'</option>';
                    ?>
                </select>
            </div>
            <button onclick="printCards()" class="sm-btn" style="background:#27ae60;">توليد البطاقات للطباعة</button>
        </div>

        <!-- Disciplinary Reports -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; color:var(--sm-secondary-color); display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-media-document"></span> التقارير الانضباطية المفصلة
            </h4>
            <p style="font-size: 0.85em; color: #666; margin-bottom: 20px;">استخراج سجل كامل للطالب مع الإحصائيات والمخالفات والإجراءات المتخذة.</p>
            <div class="sm-form-group">
                <label class="sm-label" style="font-size: 12px;">اختر الطالب:</label>
                <select id="report_student_id" class="sm-select">
                    <?php 
                    $students = SM_DB::get_students();
                    foreach($students as $s) echo '<option value="'.$s->id.'">'.$s->name.' ('.$s->class_name.')</option>';
                    ?>
                </select>
            </div>
            <button onclick="printReport()" class="sm-btn">عرض التقرير المفصل</button>
        </div>

        <!-- General Disciplinary Log -->
        <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; color:var(--sm-secondary-color); display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-list-view"></span> سجل المخالفات العام
            </h4>
            <p style="font-size: 0.85em; color: #666; margin-bottom: 20px;">طباعة قائمة بكافة المخالفات المسجلة في النظام خلال فترة محددة.</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                <div>
                    <label class="sm-label" style="font-size: 11px;">من:</label>
                    <input type="date" id="log_start_date" class="sm-input" style="font-size: 12px; padding: 8px;">
                </div>
                <div>
                    <label class="sm-label" style="font-size: 11px;">إلى:</label>
                    <input type="date" id="log_end_date" class="sm-input" style="font-size: 12px; padding: 8px;">
                </div>
            </div>
            <button onclick="printGeneralLog()" class="sm-btn" style="background: var(--sm-secondary-color);">توليد السجل العام</button>
        </div>

        <!-- Excel Templates Section -->
        <div style="grid-column: 1 / -1; background: #f8fafc; padding: 30px; border-radius: 12px; border: 2px dashed #cbd5e1; margin-top: 20px;">
            <h4 style="margin-top:0; color:var(--sm-secondary-color); display:flex; align-items:center; gap:10px;">
                <span class="dashicons dashicons-media-spreadsheet"></span> نماذج إكسل جاهزة للاستخدام
            </h4>
            <p style="font-size: 0.9em; color: #64748b; margin-bottom: 20px;">قم بتحميل النماذج التالية، املأ البيانات، ثم ارفعها في الأقسام المخصصة لتسريع عملية إدخال البيانات.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="data:text/csv;charset=utf-8,<?php echo rawurlencode("الاسم,الصف,البريد,الكود\nاسم الطالب,الصف الأول,parent@example.com,STU001"); ?>" download="students_template.csv" class="sm-btn" style="background:#fff; color:#2d3748; border:1px solid #cbd5e1; font-size:13px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span class="dashicons dashicons-download"></span> نموذج الطلاب
                </a>
                <a href="data:text/csv;charset=utf-8,<?php echo rawurlencode("الاسم,المستخدم,البريد,كلمة السر\nاسم ولي الأمر,parent_user,parent@example.com,pass123"); ?>" download="parents_template.csv" class="sm-btn" style="background:#fff; color:#2d3748; border:1px solid #cbd5e1; font-size:13px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span class="dashicons dashicons-download"></span> نموذج أولياء الأمور
                </a>
                <a href="data:text/csv;charset=utf-8,<?php echo rawurlencode("الاسم,المستخدم,البريد,المعرف الوظيفي,المسمى,الجوال,كلمة السر\nاسم المعلم,teacher_user,teacher@example.com,T100,معلم فصل,0500000000,pass123"); ?>" download="teachers_template.csv" class="sm-btn" style="background:#fff; color:#2d3748; border:1px solid #cbd5e1; font-size:13px; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span class="dashicons dashicons-download"></span> نموذج المعلمين
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function printCards() {
    const classFilter = document.getElementById('card_class_filter').value;
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=id_card'); ?>&class_name=' + encodeURIComponent(classFilter), '_blank');
}

function printReport() {
    const studentId = document.getElementById('report_student_id').value;
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=disciplinary_report'); ?>&student_id=' + studentId, '_blank');
}

function printGeneralLog() {
    const start = document.getElementById('log_start_date').value;
    const end = document.getElementById('log_end_date').value;
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=general_log'); ?>&start_date=' + start + '&end_date=' + end, '_blank');
}
</script>
