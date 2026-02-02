<?php
if (!defined('ABSPATH')) exit;
if (in_array('sm_student', (array)wp_get_current_user()->roles)) {
    echo '<p>يرجى التوجه إلى لوحة المعلومات الخاصة بك.</p>';
    return;
}
?>
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
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); position: relative; max-height: 350px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            <h3 style="margin:0; font-size: 1.1em;">اتجاهات المخالفات (آخر 30 يوم)</h3>
            <button onclick="smDownloadChart('violationTrendsChart', 'اتجاهات_المخالفات')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 200px;"><canvas id="violationTrendsChart"></canvas></div>
    </div>
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); position: relative; max-height: 350px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            <h3 style="margin:0; font-size: 1.1em;">توزيع الأنواع</h3>
            <button onclick="smDownloadChart('violationCategoriesChart', 'توزيع_الأنواع')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 200px;"><canvas id="violationCategoriesChart"></canvas></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); position: relative; max-height: 380px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            <h3 style="margin:0; font-size: 1.1em;">توزيع المخالفات حسب الحدة</h3>
            <button onclick="smDownloadChart('severityChart', 'توزيع_الحدة')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 250px;"><canvas id="severityChart"></canvas></div>
    </div>
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); position: relative; max-height: 380px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            <h3 style="margin:0; font-size: 1.1em;">أكثر الطلاب مخالفة (تكرار)</h3>
            <button onclick="smDownloadChart('topStudentsChart', 'أكثر_الطلاب_مخالفة')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 250px;"><canvas id="topStudentsChart"></canvas></div>
    </div>
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color); position: relative; max-height: 380px; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            <h3 style="margin:0; font-size: 1.1em;">توزيع المخالفات حسب الدرجة</h3>
            <button onclick="smDownloadChart('degreeChart', 'توزيع_الدرجة')" class="sm-action-btn" title="تحميل كصورة" style="background:none; border:none; color:var(--sm-text-gray); cursor:pointer;"><span class="dashicons dashicons-download"></span></button>
        </div>
        <div style="height: 250px;"><canvas id="degreeChart"></canvas></div>
    </div>
</div>




<script>
function smDownloadChart(chartId, fileName) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.download = fileName + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

(function() {
    window.smCharts = window.smCharts || {};

    const initSummaryCharts = function() {
        if (typeof Chart === 'undefined') {
            setTimeout(initSummaryCharts, 200);
            return;
        }

        const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
        const severityLabels = <?php echo json_encode(SM_Settings::get_severities()); ?>;

        const createOrUpdateChart = (id, config) => {
            if (window.smCharts[id]) {
                window.smCharts[id].destroy();
            }
            const el = document.getElementById(id);
            if (el) {
                window.smCharts[id] = new Chart(el.getContext('2d'), config);
            }
        };

        // Trends Chart
        createOrUpdateChart('violationTrendsChart', {
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
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Categories Chart
        const typeLabels = <?php echo json_encode(SM_Settings::get_violation_types()); ?>;
        createOrUpdateChart('violationCategoriesChart', {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($t) use ($typeLabels){ return $typeLabels[$t->type] ?? $t->type; }, $stats['by_type'] ?? [])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function($t){ return $t->count; }, $stats['by_type'] ?? [])); ?>,
                    backgroundColor: ['#F63049', '#D02752', '#8A244B', '#111F35', '#718096']
                }]
            },
            options: chartOptions
        });

        // Severity Chart
        createOrUpdateChart('severityChart', {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($s) use ($severityLabels){ return $severityLabels[$s->severity] ?? $s->severity; }, $stats['by_severity'] ?? [])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function($s){ return $s->count; }, $stats['by_severity'] ?? [])); ?>,
                    backgroundColor: ['#111F35', '#D02752', '#F63049']
                }]
            },
            options: chartOptions
        });

        // Top Students Chart
        createOrUpdateChart('topStudentsChart', {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($s){ return $s->name; }, $stats['top_students'] ?? [])); ?>,
                datasets: [{
                    label: 'عدد المخالفات',
                    data: <?php echo json_encode(array_map(function($s){ return $s->count; }, $stats['top_students'] ?? [])); ?>,
                    backgroundColor: '#F63049'
                }]
            },
            options: { ...chartOptions, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });

        // Degree Chart
        createOrUpdateChart('degreeChart', {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($s){ return 'الدرجة ' . $s->degree; }, $stats['by_degree'] ?? [])); ?>,
                datasets: [{
                    label: 'عدد الحالات',
                    data: <?php echo json_encode(array_map(function($s){ return $s->count; }, $stats['by_degree'] ?? [])); ?>,
                    backgroundColor: '#111F35'
                }]
            },
            options: { ...chartOptions, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    };

    if (document.readyState === 'complete') initSummaryCharts();
    else window.addEventListener('load', initSummaryCharts);
})();
</script>
