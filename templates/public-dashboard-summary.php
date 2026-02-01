<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-card-grid" style="margin-bottom: 40px;">
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">إجمالي الطلاب</div>
        <div style="font-size: 2.5em; font-weight: 900; color: var(--sm-primary-color);"><?php echo esc_html($stats['total_students'] ?? 0); ?></div>
    </div>
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">إجمالي المعلمين</div>
        <div style="font-size: 2.5em; font-weight: 900; color: var(--sm-secondary-color);"><?php echo esc_html($stats['total_teachers'] ?? 0); ?></div>
    </div>
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">مخالفات اليوم</div>
        <div style="font-size: 2.5em; font-weight: 900; color: var(--sm-accent-color);"><?php echo esc_html($stats['violations_today'] ?? 0); ?></div>
    </div>
    <div class="sm-stat-card">
        <div style="font-size: 0.85em; color: var(--sm-text-gray); margin-bottom: 10px; font-weight: 700;">الإجراءات المتخذة</div>
        <div style="font-size: 2.5em; font-weight: 900; color: var(--sm-dark-color);"><?php echo esc_html($stats['total_actions'] ?? 0); ?></div>
    </div>
</div>


<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px;">
    <!-- Trends and Categories Charts -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
        <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 15px; font-size: 1.1em;">اتجاهات المخالفات (آخر 30 يوم)</h3>
        <canvas id="violationTrendsChart" height="150"></canvas>
    </div>
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
        <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 15px; font-size: 1.1em;">توزيع الأنواع</h3>
        <canvas id="violationCategoriesChart"></canvas>
    </div>
</div>




<script>
(function() {
    const initSummaryCharts = function() {
        if (typeof Chart === 'undefined') {
            setTimeout(initSummaryCharts, 200);
            return;
        }

        // Trends Chart
        const trendsEl = document.getElementById('violationTrendsChart');
        if (trendsEl) {
            new Chart(trendsEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_map(function($t){ return date('m/d', strtotime($t->date)); }, $stats['trends'] ?? [])); ?>,
                    datasets: [{
                        label: 'المخالفات',
                        data: <?php echo json_encode(array_map(function($t){ return $t->count; }, $stats['trends'] ?? [])); ?>,
                        borderColor: '#F63049',
                        backgroundColor: 'rgba(246, 48, 73, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }

        // Categories Chart
        const catsEl = document.getElementById('violationCategoriesChart');
        if (catsEl) {
            const typeLabels = <?php echo json_encode(SM_Settings::get_violation_types()); ?>;
            new Chart(catsEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_map(function($t) use ($typeLabels){ return $typeLabels[$t->type] ?? $t->type; }, $stats['by_type'] ?? [])); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_map(function($t){ return $t->count; }, $stats['by_type'] ?? [])); ?>,
                        backgroundColor: ['#F63049', '#D02752', '#8A244B', '#111F35', '#718096']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    };

    if (document.readyState === 'complete') initSummaryCharts();
    else window.addEventListener('load', initSummaryCharts);
})();
</script>
