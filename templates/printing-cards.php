<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-printing-center" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 style="margin:0; border:none; padding:0;">مركز الطباعة والتقارير</h3>
        <div style="background: #f0f7ff; padding: 10px 20px; border-radius: 8px; border: 1px solid #c3dafe; font-size: 0.9em; color: var(--sm-primary-color); font-weight: 600;">
            إعدادات الطباعة: A4 عمودي
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
        <!-- Student ID Cards (All) -->
        <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid var(--sm-border-color); display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--sm-shadow);">
            <div>
                <div style="width: 50px; height: 50px; background: #F8FAFC; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: #3182CE;">
                    <span class="dashicons dashicons-groups" style="font-size: 28px; width: 28px; height: 28px;"></span>
                </div>
                <h4 style="margin: 0 0 10px 0; border: none; font-weight: 800; font-size: 15px;">بطاقات الطلاب (الكل)</h4>
                <p style="font-size: 11px; color: #718096; line-height: 1.6; margin-bottom: 20px;">طباعة بطاقات التعريف لكافة الطلاب في النظام أو حسب صف محدد.</p>
                <div class="sm-form-group">
                    <select id="card_class_filter" class="sm-select" style="font-size: 12px; padding: 8px;">
                        <option value="">كافة الصفوف</option>
                        <?php
                        global $wpdb;
                        $classes = $wpdb->get_col("SELECT DISTINCT class_name FROM {$wpdb->prefix}sm_students ORDER BY CAST(REPLACE(class_name, 'الصف ', '') AS UNSIGNED) ASC");
                        foreach($classes as $c) echo '<option value="'.$c.'">'.$c.'</option>';
                        ?>
                    </select>
                </div>
            </div>
            <button onclick="printCards()" class="sm-btn" style="background: #3182CE; font-size: 12px;">طباعة البطاقات</button>
        </div>

        <!-- Specific Student ID Card -->
        <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid var(--sm-border-color); display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--sm-shadow);">
            <div>
                <div style="width: 50px; height: 50px; background: #FFF5F5; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: #E53E3E;">
                    <span class="dashicons dashicons-id-alt" style="font-size: 28px; width: 28px; height: 28px;"></span>
                </div>
                <h4 style="margin: 0 0 10px 0; border: none; font-weight: 800; font-size: 15px;">بطاقة طالب محدد</h4>
                <p style="font-size: 11px; color: #718096; line-height: 1.6; margin-bottom: 20px;">استخراج بطاقة تعريفية رسمية لطالب واحد فقط بالاسم والكود.</p>
                <div class="sm-form-group">
                    <select id="specific_card_student_id" class="sm-select" style="font-size: 12px; padding: 8px;">
                        <?php
                        $students = SM_DB::get_students();
                        foreach($students as $s) echo '<option value="'.$s->id.'">'.$s->name.'</option>';
                        ?>
                    </select>
                </div>
            </div>
            <button onclick="printSpecificCard()" class="sm-btn" style="background: #E53E3E; font-size: 12px;">توليد البطاقة</button>
        </div>

        <!-- Disciplinary Reports -->
        <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid var(--sm-border-color); display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--sm-shadow);">
            <div>
                <div style="width: 50px; height: 50px; background: #F0FFF4; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: #38A169;">
                    <span class="dashicons dashicons-media-document" style="font-size: 28px; width: 28px; height: 28px;"></span>
                </div>
                <h4 style="margin: 0 0 10px 0; border: none; font-weight: 800; font-size: 15px;">التقارير الانضباطية</h4>
                <p style="font-size: 11px; color: #718096; line-height: 1.6; margin-bottom: 20px;">تقرير رسمي مفصل وشامل لسلوك الطالب، جاهز للطباعة والختم.</p>
                <div class="sm-form-group">
                    <select id="report_student_id" class="sm-select" style="font-size: 12px; padding: 8px;">
                        <?php foreach($students as $s) echo '<option value="'.$s->id.'">'.$s->name.'</option>'; ?>
                    </select>
                </div>
            </div>
            <button onclick="printReport()" class="sm-btn" style="background: #38A169; font-size: 12px;">عرض التقرير</button>
        </div>

        <!-- General Disciplinary Log -->
        <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid var(--sm-border-color); display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--sm-shadow);">
            <div>
                <div style="width: 50px; height: 50px; background: #FFF9DB; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; color: #D69E2E;">
                    <span class="dashicons dashicons-list-view" style="font-size: 28px; width: 28px; height: 28px;"></span>
                </div>
                <h4 style="margin: 0 0 10px 0; border: none; font-weight: 800; font-size: 15px;">سجل المخالفات العام</h4>
                <p style="font-size: 11px; color: #718096; line-height: 1.6; margin-bottom: 20px;">طباعة كشف كامل بكافة المخالفات المسجلة بالمدرسة خلال فترة زمنية.</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 15px;">
                    <input type="date" id="log_start_date" class="sm-input" style="font-size: 10px; padding: 5px;">
                    <input type="date" id="log_end_date" class="sm-input" style="font-size: 10px; padding: 5px;">
                </div>
            </div>
            <button onclick="printGeneralLog()" class="sm-btn" style="background: #111F35; font-size: 12px;">تحميل السجل</button>
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

function printSpecificCard() {
    const studentId = document.getElementById('specific_card_student_id').value;
    window.open('<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=id_card'); ?>&student_id=' + studentId, '_blank');
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
