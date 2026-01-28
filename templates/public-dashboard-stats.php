<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-admin-panel" dir="rtl">
    <h3>سجل سجلات الطلاب</h3>
    
    <?php $is_parent = in_array('sm_parent', (array) wp_get_current_user()->roles); ?>
    <div style="background: white; padding: 30px; border: 1px solid var(--sm-border-color); border-radius: var(--sm-radius); margin-bottom: 30px; box-shadow: var(--sm-shadow);">
        <form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)) auto; gap: 15px; align-items: end;">
            <?php if (!$is_parent): ?>
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">الطالب:</label>
                <select name="student_filter" class="sm-select">
                    <option value="">كل الطلاب</option>
                    <?php 
                    $all_students = SM_DB::get_students();
                    foreach ($all_students as $s): ?>
                        <option value="<?php echo $s->id; ?>" <?php selected(isset($_GET['student_filter']) && $_GET['student_filter'] == $s->id); ?>><?php echo esc_html($s->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">تاريخ البداية:</label>
                <input type="date" name="start_date" class="sm-input" value="<?php echo esc_attr(isset($_GET['start_date']) ? $_GET['start_date'] : ''); ?>">
            </div>

            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">تاريخ النهاية:</label>
                <input type="date" name="end_date" class="sm-input" value="<?php echo esc_attr(isset($_GET['end_date']) ? $_GET['end_date'] : ''); ?>">
            </div>
            
            <div class="sm-form-group" style="margin-bottom:0;">
                <label class="sm-label">النوع:</label>
                <select name="type_filter" class="sm-select">
                    <option value="">كل الأنواع</option>
                    <?php foreach (SM_Settings::get_violation_types() as $k => $v): ?>
                        <option value="<?php echo esc_attr($k); ?>" <?php selected(isset($_GET['type_filter']) && $_GET['type_filter'] == $k); ?>><?php echo esc_html($v); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="sm-btn">تطبيق الفلتر</button>
                <?php if (!$is_parent): ?>
                    <button type="button" onclick="document.getElementById('violation-import-form').style.display='block'" class="sm-btn sm-btn-secondary">استيراد (Excel)</button>
                <?php endif; ?>
                <button type="button" onclick="window.print()" class="sm-btn" style="background:#27ae60;">طباعة</button>
            </div>
        </form>
    </div>

    <div id="violation-import-form" style="display:none; background: #f8fafc; padding: 30px; border: 2px dashed #cbd5e0; border-radius: 12px; margin-bottom: 30px;">
        <h3 style="margin-top:0; color:var(--sm-secondary-color);">دليل استيراد السجلات (CSV)</h3>
        
        <div style="background:#fff; padding:15px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
            <p style="font-size:13px; font-weight:700; margin-bottom:10px;">هيكل ملف السجلات الصحيح:</p>
            <table style="width:100%; font-size:11px; border-collapse:collapse; text-align:center;">
                <thead>
                    <tr style="background:#edf2f7;">
                        <th style="border:1px solid #cbd5e0; padding:5px;">كود الطالب</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">النوع (سلوك/غياب/تأخر)</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الحدة (منخفضة/متوسطة/خطيرة)</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">التفاصيل</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">الإجراء المتخذ</th>
                        <th style="border:1px solid #cbd5e0; padding:5px;">المكافأة/العقوبة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border:1px solid #cbd5e0; padding:5px;">STU001</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">سلوكية</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">خطيرة</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">تعدي على الزملاء</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">فصل 3 أيام</td>
                        <td style="border:1px solid #cbd5e0; padding:5px;">حرمان من الرحلة</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('sm_admin_action', 'sm_admin_nonce'); ?>
            <div class="sm-form-group">
                <label class="sm-label">اختر ملف CSV للمخالفات:</label>
                <input type="file" name="csv_file" accept=".csv" required>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="sm_import_violations_csv" class="sm-btn" style="width:auto; background:#27ae60;">استيراد السجلات الآن</button>
                <button type="button" onclick="this.parentElement.parentElement.parentElement.style.display='none'" class="sm-btn" style="width:auto; background:var(--sm-text-gray);">إلغاء</button>
            </div>
        </form>
    </div>

    <div id="edit-record-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 550px;">
            <div class="sm-modal-header">
                <h3>تعديل بيانات المخالفة</h3>
                <button class="sm-modal-close" onclick="document.getElementById('edit-record-modal').style.display='none'">&times;</button>
            </div>
            <form method="post">
                <?php wp_nonce_field('sm_record_action', 'sm_nonce'); ?>
                <input type="hidden" name="record_id" id="edit_record_id">
                
                <div class="sm-form-group">
                    <label class="sm-label">النوع:</label>
                    <select name="type" id="edit_type" class="sm-select">
                        <?php foreach (SM_Settings::get_violation_types() as $k => $v): ?>
                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">الحدة:</label>
                    <select name="severity" id="edit_severity" class="sm-select">
                        <?php foreach (SM_Settings::get_severities() as $k => $v): ?>
                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">التفاصيل:</label>
                    <textarea name="details" id="edit_details" class="sm-textarea" rows="3"></textarea>
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">الإجراء:</label>
                    <input type="text" name="action_taken" id="edit_action_taken" class="sm-input">
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">المكافأة/العقوبة:</label>
                    <input type="text" name="reward_penalty" id="edit_reward_penalty" class="sm-input">
                </div>

                <div style="display:flex; gap:12px; margin-top: 20px; justify-content: flex-end;">
                    <button type="submit" name="sm_update_record" class="sm-btn">حفظ التغييرات</button>
                    <button type="button" onclick="document.getElementById('edit-record-modal').style.display='none'" class="sm-btn" style="background:var(--sm-text-gray);">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editSmRecord(record) {
        document.getElementById('edit_record_id').value = record.id;
        document.getElementById('edit_type').value = record.type;
        document.getElementById('edit_severity').value = record.severity;
        document.getElementById('edit_details').value = record.details;
        document.getElementById('edit_action_taken').value = record.action_taken;
        document.getElementById('edit_reward_penalty').value = record.reward_penalty;
        document.getElementById('edit-record-modal').style.display = 'flex';
    }
    </script>

    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>التاريخ</th>
                    <th>نوع المخالفة</th>
                    <th>التفاصيل</th>
                    <th>الحدة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" style="padding: 60px; text-align: center; color: var(--sm-text-gray);">
                            <span class="dashicons dashicons-clipboard" style="font-size:48px; width:48px; height:48px; margin-bottom:15px;"></span>
                            <p>لا توجد سجلات مطابقة حالياً.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $type_labels = SM_Settings::get_violation_types();
                    $severity_labels = SM_Settings::get_severities();
                    foreach ($records as $row):
                        $waMsg = rawurlencode("تنبيه من المدرسة بخصوص الطالب: {$row->student_name}\nنوع المخالفة: {$row->type}\nالتاريخ: ".date('Y-m-d', strtotime($row->created_at))."\nالتفاصيل: {$row->details}");
                    ?>
                        <tr id="record-row-<?php echo $row->id; ?>">
                            <td>
                                <div style="font-weight: 800;"><?php echo esc_html($row->student_name); ?></div>
                                <div style="font-size: 11px; color: var(--sm-text-gray);"><?php echo esc_html($row->class_name); ?></div>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($row->created_at)); ?></td>
                            <td><span class="sm-badge sm-badge-low" style="background: var(--sm-pastel-red); color: var(--sm-primary-color);"><?php echo $type_labels[$row->type] ?? $row->type; ?></span></td>
                            <td>
                                <div style="max-width: 300px; font-size: 13px; color: #4a5568;"><?php echo esc_html($row->details); ?></div>
                            </td>
                            <td>
                                <span class="sm-badge sm-badge-<?php echo esc_attr($row->severity); ?>">
                                    <?php echo $severity_labels[$row->severity] ?? $row->severity; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=single_violation&record_id=' . $row->id); ?>" target="_blank" class="sm-btn sm-btn-outline" style="padding: 5px;" title="طباعة"><span class="dashicons dashicons-printer"></span></a>
                                    <?php if (current_user_can('إدارة_المخالفات')): ?>
                                        <button onclick="editSmRecord(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="sm-btn sm-btn-outline" style="padding: 5px;" title="تعديل"><span class="dashicons dashicons-edit"></span></button>
                                        <button onclick="confirmDeleteRecord(<?php echo $row->id; ?>)" class="sm-btn sm-btn-outline" style="padding: 5px; color:#e53e3e;" title="حذف"><span class="dashicons dashicons-trash"></span></button>
                                    <?php endif; ?>
                                    <a href="https://wa.me/?text=<?php echo $waMsg; ?>" target="_blank" class="sm-btn sm-btn-outline" style="padding: 5px; color:#38a169;" title="واتساب"><span class="dashicons dashicons-whatsapp"></span></a>
                                </div>
                                <?php if ($row->status === 'pending' && current_user_can('إدارة_المخالفات')): ?>
                                    <div style="margin-top: 8px; display: flex; gap: 5px;">
                                        <button onclick="updateRecordStatus(<?php echo $row->id; ?>, 'accepted')" class="sm-btn" style="background: #38a169; font-size: 10px; padding: 4px 8px;">اعتماد</button>
                                        <button onclick="updateRecordStatus(<?php echo $row->id; ?>, 'rejected')" class="sm-btn" style="background: #e53e3e; font-size: 10px; padding: 4px 8px;">رفض</button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Delete Record Confirmation Modal -->
    <div id="delete-record-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 400px; text-align: center;">
            <div style="color: #e53e3e; font-size: 40px; margin-bottom: 15px;"><span class="dashicons dashicons-warning" style="font-size:40px;"></span></div>
            <h3 style="margin:0 0 10px 0; border:none;">تأكيد حذف المخالفة</h3>
            <p>هل أنت متأكد من حذف هذا السجل نهائياً؟</p>
            <input type="hidden" id="confirm_delete_record_id">
            <div style="display: flex; gap: 15px; margin-top: 25px;">
                <button onclick="executeDeleteRecord()" class="sm-btn" style="background: #e53e3e;">حذف نهائي</button>
                <button onclick="document.getElementById('delete-record-modal').style.display='none'" class="sm-btn" style="background: #cbd5e0; color: #2d3748;">تراجع</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        window.confirmDeleteRecord = function(id) {
            document.getElementById('confirm_delete_record_id').value = id;
            document.getElementById('delete-record-modal').style.display = 'flex';
        };

        window.executeDeleteRecord = function() {
            const id = document.getElementById('confirm_delete_record_id').value;
            const formData = new FormData();
            formData.append('action', 'sm_delete_record_ajax');
            formData.append('record_id', id);
            formData.append('nonce', '<?php echo wp_create_nonce("sm_record_action"); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    smShowNotification('تم حذف السجل بنجاح');
                    document.getElementById('record-card-' + id).remove();
                    document.getElementById('delete-record-modal').style.display = 'none';
                }
            });
        };
    })();
    </script>

    <style>
    .sm-record-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.2s; }
    .sm-action-icon-btn { width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; text-decoration: none; font-size: 16px; }
    </style>
</div>
<style>
@media print {
    body * { visibility: hidden; }
    .sm-admin-panel, .sm-admin-panel * { visibility: visible; }
    .sm-admin-panel { position: absolute; left: 0; top: 0; width: 100%; }
    .no-print { display: none !important; }
}
</style>
