<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-content-wrapper" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="margin:0; border:none; padding:0;">مراجعة بلاغات المعلمين</h3>
        <span class="sm-badge sm-badge-high"><?php echo count($records); ?> بلاغ معلق</span>
    </div>

    <div style="background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; padding: 15px; margin-bottom: 25px; color: #c53030; font-size: 0.9em; display: flex; align-items: center; gap: 10px;">
        <span class="dashicons dashicons-warning"></span>
        <span>هذه البلاغات تم تسجيلها بواسطة المعلمين وهي بانتظار اعتماد الإدارة أو مسؤول الانضباط لاتخاذ القرار النهائي.</span>
    </div>

    <div class="sm-records-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;">
        <?php if (empty($records)): ?>
            <div style="grid-column: 1 / -1; padding: 60px; text-align: center; background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); color: var(--sm-text-gray);">
                <span class="dashicons dashicons-yes-alt" style="font-size: 50px; width:50px; height:50px; margin-bottom:10px; color:#38a169;"></span>
                <p>عمل رائع! لا توجد بلاغات معلقة حالياً.</p>
            </div>
        <?php else: ?>
            <?php 
            $type_labels = SM_Settings::get_violation_types();
            $severity_labels = SM_Settings::get_severities();
            foreach ($records as $row): 
                $teacher = get_userdata($row->teacher_id);
            ?>
                <div class="sm-record-card" style="background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); position: relative; border-right: 6px solid #e53e3e;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div>
                            <div style="font-weight: 800; color: var(--sm-secondary-color); font-size: 1.2em;"><?php echo esc_html($row->student_name); ?></div>
                            <div style="font-size: 0.85em; color: #718096; margin-top: 5px;">
                                <span class="dashicons dashicons-welcome-learn-more" style="font-size:14px;"></span> <?php echo esc_html($row->class_name); ?> | 
                                <span class="dashicons dashicons-clock" style="font-size:14px;"></span> <?php echo date('Y-m-d H:i', strtotime($row->created_at)); ?>
                            </div>
                        </div>
                        <span class="sm-badge sm-badge-<?php echo esc_attr($row->severity); ?>">
                            <?php echo $severity_labels[$row->severity] ?? $row->severity; ?>
                        </span>
                    </div>

                    <div style="background: #f7fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #edf2f7;">
                        <div style="margin-bottom: 8px; font-weight: 700; color: #4a5568;">
                            <span class="dashicons dashicons-businessman" style="font-size:16px;"></span> مقدم البلاغ: 
                            <span style="color:var(--sm-primary-color);"><?php echo $teacher ? esc_html($teacher->display_name) : 'غير معروف'; ?></span>
                        </div>
                        <div style="margin-bottom: 8px;"><strong>نوع المخالفة:</strong> <?php echo $type_labels[$row->type] ?? $row->type; ?></div>
                        <div style="color: #2d3748; line-height: 1.6; font-style: italic;">"<?php echo esc_html($row->details); ?>"</div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button onclick="reviewReportDecision(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="sm-btn" style="flex:2; background: var(--sm-primary-color);">اتخاذ قرار / اعتماد</button>
                        <button onclick="updateRecordStatus(<?php echo $row->id; ?>, 'rejected')" class="sm-btn" style="flex:1; background: #e53e3e;">رفض البلاغ</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Decision Modal -->
    <div id="decision-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 600px;">
            <div class="sm-modal-header">
                <h3>قرار الإدارة النهائي</h3>
                <button class="sm-modal-close" onclick="document.getElementById('decision-modal').style.display='none'">&times;</button>
            </div>
            <form method="post" action="">
                <?php wp_nonce_field('sm_record_action', 'sm_nonce'); ?>
                <input type="hidden" name="record_id" id="decision_record_id">
                <input type="hidden" name="status" value="accepted">
                
                <div class="sm-form-group">
                    <label class="sm-label">تعديل الإجراء المتخذ (اختياري):</label>
                    <input type="text" name="action_taken" id="decision_action" class="sm-input" placeholder="مثال: فصل يومين، استدعاء رسمي...">
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">المكافأة أو العقوبة الإضافية:</label>
                    <input type="text" name="reward_penalty" id="decision_reward" class="sm-input">
                </div>

                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" name="sm_update_record" class="sm-btn" style="background:#38a169;">اعتماد وحفظ القرار</button>
                    <button type="button" onclick="document.getElementById('decision-modal').style.display='none'" class="sm-btn" style="background:#cbd5e0; color:#2d3748;">تراجع</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function reviewReportDecision(record) {
        document.getElementById('decision_record_id').value = record.id;
        document.getElementById('decision_action').value = record.action_taken || '';
        document.getElementById('decision_reward').value = record.reward_penalty || '';
        document.getElementById('decision-modal').style.display = 'flex';
    }
    </script>
</div>
