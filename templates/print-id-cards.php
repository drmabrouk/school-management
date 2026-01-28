<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>بطاقات هوية الطلاب</title>
    <style>
        @page { size: portrait; margin: 10mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; margin: 0; padding: 20px; color: #2d3748; }
        .cards-container { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; justify-items: center; }
        .id-card { 
            width: 85mm; height: 55mm; background: #fff; border: 1px solid #e2e8f0; 
            border-radius: 12px; padding: 0; position: relative; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            overflow: hidden; display: flex; flex-direction: column;
        }
        .header { background: var(--sm-primary-color, #0073aa); color: white; padding: 8px 12px; text-align: center; font-weight: 800; font-size: 0.85em; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .content { display: flex; flex: 1; padding: 12px; align-items: center; gap: 12px; }
        .photo-box img { width: 55px; height: 55px; border-radius: 8px; object-fit: cover; border: 2px solid #f1f5f9; }
        .info { flex: 1; min-width: 0; }
        .info p { margin: 2px 0; font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .info strong { color: #4a5568; }
        .barcode img { width: 50px; height: 50px; border: 1px solid #edf2f7; padding: 2px; }
        .footer { background: #f8fafc; border-top: 1px solid #edf2f7; padding: 4px; text-align: center; font-size: 9px; color: #718096; font-weight: 600; }
        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none !important; }
            .id-card { box-shadow: none; break-inside: avoid; border: 1px solid #ddd; }
        }
        <?php $print_settings = get_option('sm_print_settings'); echo $print_settings['custom_css'] ?? ''; ?>
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #27ae60; color: white; border: none; cursor: pointer; border-radius: 5px;">بدء الطباعة</button>
    </div>
    <div class="cards-container">
        <?php 
        $school = SM_Settings::get_school_info();
        foreach ($students as $s): ?>
        <div class="id-card" style="--sm-primary-color: <?php echo SM_Settings::get_appearance()['primary_color']; ?>;">
            <div class="header">
                <?php if ($school['school_logo']): ?>
                    <img src="<?php echo esc_url($school['school_logo']); ?>" style="height: 18px; filter: brightness(0) invert(1);">
                <?php endif; ?>
                <span><?php echo esc_html($school['school_name']); ?></span>
            </div>
            <div class="content">
                <div class="photo-box">
                    <?php if ($s->photo_url): ?>
                        <img src="<?php echo esc_url($s->photo_url); ?>">
                    <?php else: ?>
                        <div style="width: 55px; height: 55px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 25px; color: #cbd5e0;">👤</div>
                    <?php endif; ?>
                </div>
                <div class="info">
                    <p><strong>الطالب:</strong> <?php echo esc_html($s->name); ?></p>
                    <p><strong>الصف:</strong> <?php echo esc_html($s->class_name); ?></p>
                    <p><strong>الكود:</strong> <?php echo esc_html($s->student_code); ?></p>
                </div>
                <div class="barcode">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($s->student_code); ?>" alt="QR Code">
                </div>
            </div>
            <div class="footer">
                <?php echo esc_html($school['phone']); ?> | <?php echo esc_html($school['address']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
