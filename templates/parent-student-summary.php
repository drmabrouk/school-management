<?php if (!defined('ABSPATH')) exit; ?>
<div style="background: #fff; border: 1px solid var(--sm-border-color); border-radius: 16px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
    <div style="background: linear-gradient(135deg, var(--sm-primary-color) 0%, #2c3e50 100%); padding: 40px; display: flex; gap: 30px; align-items: center; color: white;">
        <div style="flex-shrink: 0;">
            <?php if ($student->photo_url): ?>
                <img src="<?php echo esc_url($student->photo_url); ?>" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 16px rgba(0,0,0,0.2);">
            <?php else: ?>
                <div style="width: 130px; height: 130px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 50px; border: 4px solid rgba(255,255,255,0.3);">👤</div>
            <?php endif; ?>
        </div>
        <div style="flex: 1;">
            <h2 style="margin: 0 0 12px 0; border: none; padding: 0; color: white; font-size: 2em; font-weight: 800;"><?php echo esc_html($student->name); ?></h2>
            <div style="display: flex; gap: 25px; font-size: 1.1em; opacity: 0.9;">
                <span style="display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-welcome-learn-more"></span> <?php echo esc_html($student->class_name); ?></span>
                <span style="display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-id"></span> كود: <?php echo esc_html($student->student_code); ?></span>
            </div>
        </div>
        <div style="text-align: left;">
            <a href="<?php echo admin_url('admin-ajax.php?action=sm_print&print_type=disciplinary_report&student_id=' . $student->id); ?>" target="_blank" class="sm-btn" style="background: white !important; color: var(--sm-primary-color) !important; width: auto; font-size: 14px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">📂 تحميل الملف الشامل PDF</a>
        </div>
    </div>
</div>

<div class="sm-card-grid" style="margin-bottom: 40px;">
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">إجمالي المخالفات</div>
        <div style="font-size: 2.5em; font-weight: 900; color: #e74c3c;"><?php echo $stats['total'] ?? 0; ?></div>
    </div>
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">مخالفات خطيرة</div>
        <div style="font-size: 2.5em; font-weight: 900; color: #c0392b;"><?php echo $stats['high_severity_count'] ?? 0; ?></div>
    </div>
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">النوع الأكثر تكراراً</div>
        <div style="font-size: 1.2em; font-weight: 700; color: var(--sm-secondary-color); margin-top: 15px;">
            <?php 
            $types = SM_Settings::get_violation_types();
            echo isset($types[$stats['frequent_type']]) ? $types[$stats['frequent_type']] : 'لا يوجد'; 
            ?>
        </div>
    </div>
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">آخر إجراء متخذ</div>
        <div style="font-size: 1.1em; font-weight: 700; color: #27ae60; margin-top: 15px;"><?php echo $stats['last_action'] ?: 'لا يوجد'; ?></div>
    </div>
</div>

<div style="background: #fff; padding: 30px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
    <h3 style="margin-top:0;">توزيع المخالفات حسب النوع</h3>
    <div style="max-width: 500px; margin: 0 auto;">
        <canvas id="parentStudentChart"></canvas>
    </div>
</div>

<script>
(function() {
    const initParentChart = function() {
        if (typeof Chart === 'undefined') {
            setTimeout(initParentChart, 200);
            return;
        }
        const ctx = document.getElementById('parentStudentChart');
        if (!ctx) return;
        
        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: [<?php foreach($stats['by_type'] as $st) echo "'" . (isset($types[$st->type]) ? $types[$st->type] : $st->type) . "',"; ?>],
                datasets: [{
                    data: [<?php foreach($stats['by_type'] as $st) echo $st->count . ","; ?>],
                    backgroundColor: ['#3498db', '#e74c3c', '#f1c40f', '#2ecc71', '#9b59b6', '#1abc9c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    };
    if (document.readyState === 'complete') initParentChart();
    else window.addEventListener('load', initParentChart);
})();
</script>
