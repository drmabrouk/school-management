<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إشعار مخالفة - <?php echo esc_html($record->student_name); ?></title>
    <style>
        @page { size: portrait; margin: 15mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 0; background: #fff; line-height: 1.6; color: #2d3748; }
        .receipt { border: 2px solid #2d3748; padding: 30px; max-width: 600px; margin: 0 auto; border-radius: 15px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px double #2d3748; margin-bottom: 25px; padding-bottom: 15px; }
        .row { display: flex; margin-bottom: 12px; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; }
        .label { font-weight: 800; width: 160px; color: #4a5568; }
        .value { flex: 1; }
        @media print { 
            .no-print { display: none !important; }
            .receipt { box-shadow: none; border-width: 1px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #27ae60; color: white; border: none; cursor: pointer; border-radius: 5px;">طباعة الإشعار</button>
    </div>
    <?php $school = SM_Settings::get_school_info(); ?>
    <div class="receipt">
        <div class="header">
            <?php if ($school['school_logo']): ?>
                <img src="<?php echo esc_url($school['school_logo']); ?>" style="max-height: 60px; margin-bottom: 10px;">
            <?php endif; ?>
            <h2 style="margin: 0;"><?php echo esc_html($school['school_name']); ?></h2>
            <p style="margin: 5px 0; font-weight: bold; color: #0073aa;">إشعار مخالفة سلوكية</p>
        </div>
        <div class="row">
            <div class="label">اسم الطالب:</div>
            <div class="value"><?php echo esc_html($record->student_name); ?></div>
        </div>
        <div class="row">
            <div class="label">الصف الدراسي:</div>
            <div class="value"><?php echo esc_html($record->class_name); ?></div>
        </div>
        <div class="row">
            <div class="label">التاريخ:</div>
            <div class="value"><?php echo esc_html($record->created_at); ?></div>
        </div>
        <div class="row">
            <div class="label">نوع المخالفة:</div>
            <div class="value"><?php 
                $types = SM_Settings::get_violation_types();
                echo isset($types[$record->type]) ? esc_html($types[$record->type]) : esc_html($record->type); 
            ?></div>
        </div>
        <div class="row">
            <div class="label">درجة الحدة:</div>
            <div class="value"><?php 
                $severities = SM_Settings::get_severities();
                echo isset($severities[$record->severity]) ? esc_html($severities[$record->severity]) : esc_html($record->severity); 
            ?></div>
        </div>
        <div class="row">
            <div class="label">التفاصيل:</div>
            <div class="value"><?php echo nl2br(esc_html($record->details)); ?></div>
        </div>
        <div class="row">
            <div class="label">الإجراء المتخذ:</div>
            <div class="value"><?php echo esc_html($record->action_taken); ?></div>
        </div>
        <div style="margin-top: 30px; text-align: left;">
            <p>توقيع المشرف التربوي</p>
            <br>
            <p>..........................</p>
        </div>
    </div>
</body>
</html>
