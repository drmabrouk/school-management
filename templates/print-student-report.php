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
        body { font-family: 'Rubik', sans-serif; padding: 0; color: #1a202c; line-height: 1.6; background: #fff; }
        .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #111F35; padding-bottom: 15px; margin-bottom: 30px; }
        .student-info { display: grid; grid-template-columns: auto 1fr auto; gap: 30px; margin-bottom: 40px; border: 1px solid #E2E8F0; padding: 25px; border-radius: 12px; align-items: center; background: #F8FAFC; }
        .stats-box { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-item { border: 1px solid #E2E8F0; padding: 20px; text-align: center; border-radius: 12px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
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
        <div class="report-header">
            <div style="text-align: right; flex: 1;">
                <h2 style="margin: 0; color: #111F35; font-weight: 900;"><?php echo esc_html($school['school_name']); ?></h2>
                <p style="margin: 5px 0; font-size: 13px; font-weight: 600; color: #4A5568;">وزارة التعليم - إدارة الانضباط المدرسي</p>
                <p style="margin: 2px 0; font-size: 12px; color: #718096;"><?php echo esc_html($school['address']); ?></p>
            </div>
            <div style="flex: 1; text-align: center;">
                <?php if ($school['school_logo']): ?>
                    <img src="<?php echo esc_url($school['school_logo']); ?>" style="max-height: 90px; object-fit: contain;">
                <?php endif; ?>
            </div>
            <div style="text-align: left; flex: 1;">
                <h3 style="margin: 0; color: #F63049; font-weight: 800;">التقرير الانضباطي الرسمي</h3>
                <p style="margin: 5px 0; font-size: 12px; color: #718096;">الرقم المرجعي: <?php echo 'REP-' . date('Ym') . '-' . $student->id; ?></p>
                <p style="margin: 2px 0; font-size: 12px; color: #718096;">تاريخ الطباعة: <?php echo date('Y/m/d'); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="student-info">
        <div style="text-align: center;">
            <?php if ($student->photo_url): ?>
                <img src="<?php echo esc_url($student->photo_url); ?>" style="width: 110px; height: 110px; border-radius: 12px; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <?php else: ?>
                <div style="width: 110px; height: 110px; border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 50px; border: 3px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: #CBD5E0;">👤</div>
            <?php endif; ?>
        </div>
        <div style="padding-right: 20px;">
            <h2 style="margin:0 0 15px 0; color:#111F35; border:none; padding:0; font-size: 24px; font-weight: 800;"><?php echo esc_html($student->name); ?></h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div><span style="color: #718096; font-size: 12px; display: block;">الصف والشعبة</span><strong style="color: #2D3748;"><?php echo SM_Settings::format_grade_name($student->class_name, $student->section); ?></strong></div>
                <div><span style="color: #718096; font-size: 12px; display: block;">الرقم الأكاديمي</span><strong style="color: #2D3748; font-family: sans-serif;"><?php echo esc_html($student->student_code); ?></strong></div>
                <div><span style="color: #718096; font-size: 12px; display: block;">تاريخ التسجيل</span><strong style="color: #2D3748;"><?php echo esc_html($student->registration_date); ?></strong></div>
                <div><span style="color: #718096; font-size: 12px; display: block;">الجنسية</span><strong style="color: #2D3748;"><?php echo esc_html($student->nationality); ?></strong></div>
                <div><span style="color: #718096; font-size: 12px; display: block;">هاتف ولي الأمر</span><strong style="color: #2D3748;"><?php echo esc_html($student->guardian_phone); ?></strong></div>
                <div><span style="color: #718096; font-size: 12px; display: block;">البريد الإلكتروني</span><strong style="color: #2D3748;"><?php echo esc_html($student->parent_email); ?></strong></div>
            </div>
        </div>
        <div style="text-align: center; border-right: 1px solid #CBD5E0; padding-right: 30px;">
            <div style="background: #111F35; color: #fff; padding: 10px 20px; border-radius: 8px;">
                <div style="font-size: 10px; opacity: 0.8;">إجمالي النقاط المسجلة</div>
                <div style="font-size: 24px; font-weight: 900;"><?php echo count($records); ?></div>
            </div>
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

    <div style="margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; text-align: center;">
        <div>
            <p style="font-weight: 700; margin-bottom: 40px; color: #4A5568;">توقيع رائد الفصل</p>
            <div style="border-bottom: 1px dashed #718096; width: 150px; margin: 0 auto;"></div>
        </div>
        <div>
            <p style="font-weight: 700; margin-bottom: 40px; color: #4A5568;">توقيع المشرف التربوي</p>
            <div style="border-bottom: 1px dashed #718096; width: 150px; margin: 0 auto;"></div>
        </div>
        <div>
            <p style="font-weight: 700; margin-bottom: 40px; color: #4A5568;">مصادقة مدير المدرسة</p>
            <div style="border-bottom: 1px dashed #718096; width: 150px; margin: 0 auto;"></div>
        </div>
    </div>
    <?php if (!empty($print_settings['footer'])): ?>
        <div class="custom-print-footer" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; text-align: center; font-size: 12px;">
            <?php echo $print_settings['footer']; ?>
        </div>
    <?php endif; ?>
</body>
</html>
