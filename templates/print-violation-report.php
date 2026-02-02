<?php
if (!defined('ABSPATH')) exit;

$school = SM_Settings::get_school_info();
$academic = SM_Settings::get_academic_structure();
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تقرير مخالفات الطلاب - <?php echo esc_html($school['school_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Rubik', sans-serif; margin: 0; padding: 40px; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #F63049; padding-bottom: 20px; margin-bottom: 30px; }
        .school-info h1 { margin: 0; font-size: 24px; font-weight: 900; }
        .school-info p { margin: 5px 0 0 0; color: #666; font-size: 14px; }
        .logo img { max-height: 80px; }
        .report-title { text-align: center; margin-bottom: 30px; }
        .report-title h2 { display: inline-block; padding: 10px 40px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin: 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #111F35; color: white; padding: 12px 8px; font-size: 13px; text-align: center; border: 1px solid #111F35; }
        td { padding: 10px 8px; border: 1px solid #e2e8f0; font-size: 12px; text-align: center; }
        tr:nth-child(even) { background: #fcfcfc; }

        .footer { position: fixed; bottom: 30px; left: 40px; right: 40px; display: flex; justify-content: space-between; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="school-info">
            <h1><?php echo esc_html($school['school_name']); ?></h1>
            <p><?php echo esc_html($school['school_principal_name']); ?></p>
            <p><?php echo date_i18n('l j F Y'); ?></p>
        </div>
        <div class="logo">
            <?php if ($school['school_logo']): ?>
                <img src="<?php echo esc_url($school['school_logo']); ?>" alt="Logo">
            <?php endif; ?>
        </div>
    </div>

    <div class="report-title">
        <h2>تقرير سجلات مخالفات الطلاب</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>اسم الطالب</th>
                <th>الصف</th>
                <th>البند</th>
                <th>النقاط</th>
                <th>تكرار</th>
                <th>الإجراء المتخذ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime($r->created_at)); ?></td>
                    <td style="font-weight:700; text-align:right;"><?php echo esc_html($r->student_name); ?></td>
                    <td><?php echo esc_html($r->class_name); ?> - <?php echo esc_html($r->section); ?></td>
                    <td><?php echo esc_html($r->violation_code); ?></td>
                    <td style="font-weight:700; color:#F63049;"><?php echo (int)$r->points; ?></td>
                    <td><?php echo (int)$r->recurrence_count; ?></td>
                    <td style="text-align:right;"><?php echo esc_html($r->action_taken); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <div>نظام إدارة المدرسة - تم الاستخراج بواسطة: <?php echo wp_get_current_user()->display_name; ?></div>
        <div>صفحة 1 من 1</div>
    </div>

    <div class="no-print" style="margin-top: 40px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #F63049; color: white; border: none; border-radius: 5px; cursor: pointer; font-family: inherit;">طباعة التقرير</button>
    </div>
</body>
</html>
