<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير الطالب الانضباطي - <?php echo esc_html($student->name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Rubik', sans-serif; padding: 0; color: #1a202c; line-height: 1.4; background: #fff; }
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #2d3748; padding-bottom: 20px; margin-bottom: 30px; }
        .student-info { display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 20px; margin-bottom: 30px; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; align-items: center; }
        .stats-box { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-item { border: 1px solid #e2e8f0; padding: 15px; text-align: center; border-radius: 10px; background: #fff; }
        .stat-item h4 { margin: 0 0 8px 0; color: #4a5568; font-size: 0.9em; text-transform: uppercase; }
        .stat-item span { font-size: 1.8em; font-weight: 800; color: #2d3748; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em; }
        th, td { border: 1px solid #e2e8f0; padding: 12px 10px; text-align: right; }
        th { background: #f1f5f9; color: #475569; font-weight: 700; }
        .severity-high { color: #e53e3e; font-weight: 700; }
        .severity-medium { color: #dd6b20; }
        .severity-low { color: #3182ce; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .student-info { box-shadow: none; }
        }
        <?php $print_settings = get_option('sm_print_settings'); echo $print_settings['custom_css'] ?? ''; ?>
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #27ae60; color: white; border: none; cursor: pointer; border-radius: 5px;">طباعة التقرير (أو حفظ كـ PDF)</button>
    </div>

    <?php 
    $school = SM_Settings::get_school_info(); 
    $print_settings = get_option('sm_print_settings');
    ?>
    
    <?php if (!empty($print_settings['header'])): ?>
        <div class="custom-print-header"><?php echo $print_settings['header']; ?></div>
    <?php else: ?>
        <div class="report-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="text-align: right; flex: 1;">
                <h2 style="margin: 0; color: #0073aa;"><?php echo esc_html($school['school_name']); ?></h2>
                <p style="margin: 5px 0; font-size: 14px;"><?php echo esc_html($school['address']); ?></p>
                <p style="margin: 5px 0; font-size: 14px;"><?php echo esc_html($school['phone']); ?></p>
            </div>
            <div style="flex: 1; text-align: center;">
                <?php if ($school['school_logo']): ?>
                    <img src="<?php echo esc_url($school['school_logo']); ?>" style="max-height: 80px;">
                <?php endif; ?>
            </div>
            <div style="text-align: left; flex: 1;">
                <h3 style="margin: 0;">سجل السلوك والانضباط</h3>
                <p style="margin: 5px 0; font-size: 14px;">تاريخ الصدور: <?php echo date('Y-m-d'); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="student-info">
        <div style="text-align: center;">
            <?php if ($student->photo_url): ?>
                <img src="<?php echo esc_url($student->photo_url); ?>" style="width: 100px; height: 100px; border-radius: 8px; object-fit: cover; border: 2px solid #edf2f7;">
            <?php else: ?>
                <div style="width: 100px; height: 100px; border-radius: 8px; background: #f7fafc; display: flex; align-items: center; justify-content: center; font-size: 40px; border: 2px solid #edf2f7;">👤</div>
            <?php endif; ?>
        </div>
        <div>
            <h2 style="margin:0 0 10px 0; color:#2d3748; border:none; padding:0;"><?php echo esc_html($student->name); ?></h2>
            <table style="margin:0; font-size: 13px;">
                <tr><td style="border:none; padding:2px 0;"><strong>الصف الدراسي:</strong></td><td style="border:none; padding:2px 10px;"><?php echo esc_html($student->class_name); ?></td></tr>
                <tr><td style="border:none; padding:2px 0;"><strong>الرقم الأكاديمي:</strong></td><td style="border:none; padding:2px 10px;"><?php echo esc_html($student->student_code); ?></td></tr>
            </table>
        </div>
        <div style="text-align: left;">
            <p style="margin:0; font-size:12px;">تاريخ الصدور: <?php echo date('Y-m-d'); ?></p>
            <p style="margin:5px 0 0 0; font-size:12px;">وقت الطباعة: <?php echo date('H:i'); ?></p>
        </div>
    </div>

    <h3>ملخص إحصائي</h3>
    <div class="stats-box">
        <div class="stat-item">
            <h4>إجمالي الملاحظات</h4>
            <span><?php echo $stats['total']; ?></span>
        </div>
        <div class="stat-item">
            <h4>توزيع المخالفات</h4>
            <div style="font-size: 12px; margin-top: 5px;">
                <?php 
                $types_labels = SM_Settings::get_violation_types();
                foreach ($stats['by_type'] as $st): ?>
                    <?php echo (isset($types_labels[$st->type]) ? $types_labels[$st->type] : $st->type) . ': ' . $st->count; ?> |
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <h3>سجل المخالفات والإجراءات</h3>
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>النوع</th>
                <th>الحدة</th>
                <th>التفاصيل</th>
                <th>الإجراء المتخذ</th>
                <th>العقوبات / المكافآت</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="6">لا توجد سجلات مسجلة لهذا الطالب.</td></tr>
            <?php else: ?>
                <?php 
                $severity_labels = SM_Settings::get_severities();
                foreach ($records as $r): ?>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime($r->created_at)); ?></td>
                    <td><?php echo isset($types_labels[$r->type]) ? $types_labels[$r->type] : $r->type; ?></td>
                    <td class="<?php echo $r->severity === 'high' ? 'severity-high' : ''; ?>">
                        <?php echo isset($severity_labels[$r->severity]) ? $severity_labels[$r->severity] : $r->severity; ?>
                    </td>
                    <td><?php echo esc_html($r->details); ?></td>
                    <td><?php echo esc_html($r->action_taken); ?></td>
                    <td><?php echo esc_html($r->reward_penalty); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 50px; display: flex; justify-content: space-between;">
        <div style="text-align: center;">
            <p>توقيع المشرف التربوي</p>
            <br><br>
            <p>..........................</p>
        </div>
        <div style="text-align: center;">
            <p>ختم الإدارة</p>
            <br><br>
            <p>..........................</p>
        </div>
    </div>
    <?php if (!empty($print_settings['footer'])): ?>
        <div class="custom-print-footer" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; text-align: center; font-size: 12px;">
            <?php echo $print_settings['footer']; ?>
        </div>
    <?php endif; ?>
</body>
</html>
