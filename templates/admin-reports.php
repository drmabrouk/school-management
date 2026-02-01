<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-reports-container" dir="rtl">
    <?php
    $labels = SM_Settings::get_violation_types();
    $severity_labels = SM_Settings::get_severities();
    $all_labels = array_merge($labels, $severity_labels);
    ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 40px;">
        <div style="background: #fff; padding: 25px; border: 1px solid var(--sm-border-color); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">توزيع المخالفات حسب النوع</h4>
            <canvas id="typeChart" style="max-height: 250px;"></canvas>
        </div>
        <div style="background: #fff; padding: 25px; border: 1px solid var(--sm-border-color); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">توزيع المخالفات حسب الحدة</h4>
            <canvas id="severityChart" style="max-height: 250px;"></canvas>
        </div>
        <div style="background: #fff; padding: 25px; border: 1px solid var(--sm-border-color); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">أكثر الطلاب مخالفة (تكرار)</h4>
            <canvas id="topStudentsChart" style="max-height: 250px;"></canvas>
        </div>
        <div style="background: #fff; padding: 25px; border: 1px solid var(--sm-border-color); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">توزيع المخالفات حسب الدرجة</h4>
            <canvas id="degreeChart" style="max-height: 250px;"></canvas>
        </div>
    </div>

    <script>
    (function() {
        const initCharts = function() {
            if (typeof Chart === 'undefined') {
                console.warn('SM Reports: Chart.js not loaded yet, retrying...');
                setTimeout(initCharts, 200);
                return;
            }

            const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };

            // Type Chart
            const typeEl = document.getElementById('typeChart');
            if (typeEl) {
                new Chart(typeEl, {
                    type: 'pie',
                    data: {
                    labels: [<?php if(!empty($stats['by_type'] ?? [])) foreach($stats['by_type'] as $s) echo '"'.esc_js($all_labels[$s->type] ?? ($s->type ?? '---')).'",'; ?>],
                        datasets: [{
                        data: [<?php if(!empty($stats['by_type'] ?? [])) foreach($stats['by_type'] as $s) echo ($s->count ?? 0).','; ?>],
                            backgroundColor: ['#F63049', '#D02752', '#8A244B', '#111F35', '#718096']
                        }]
                    },
                    options: chartOptions
                });
            }

            // Severity Chart
            const sevEl = document.getElementById('severityChart');
            if (sevEl) {
                new Chart(sevEl, {
                    type: 'doughnut',
                    data: {
                    labels: [<?php if(!empty($stats['by_severity'] ?? [])) foreach($stats['by_severity'] as $s) echo '"'.esc_js($all_labels[$s->severity] ?? ($s->severity ?? '---')).'",'; ?>],
                        datasets: [{
                        data: [<?php if(!empty($stats['by_severity'] ?? [])) foreach($stats['by_severity'] as $s) echo ($s->count ?? 0).','; ?>],
                            backgroundColor: ['#111F35', '#D02752', '#F63049']
                        }]
                    },
                    options: chartOptions
                });
            }

            // Top Students Chart
            const topEl = document.getElementById('topStudentsChart');
            if (topEl) {
                new Chart(topEl, {
                    type: 'bar',
                    data: {
                    labels: [<?php if(!empty($stats['top_students'] ?? [])) foreach($stats['top_students'] as $s) echo '"'.esc_js($s->name ?? '---').'",'; ?>],
                        datasets: [{
                            label: 'عدد المخالفات',
                        data: [<?php if(!empty($stats['top_students'] ?? [])) foreach($stats['top_students'] as $s) echo ($s->count ?? 0).','; ?>],
                            backgroundColor: '#F63049'
                        }]
                    },
                    options: { ...chartOptions, scales: { y: { beginAtZero: true } } }
                });
            }

            // Degree Chart
            const degreeEl = document.getElementById('degreeChart');
            if (degreeEl) {
                new Chart(degreeEl, {
                    type: 'bar',
                    data: {
                    labels: [<?php if(!empty($stats['by_degree'] ?? [])) foreach($stats['by_degree'] as $s) echo '"الدرجة '.esc_js($s->degree ?? '---').'",'; ?>],
                        datasets: [{
                            label: 'عدد الحالات',
                        data: [<?php if(!empty($stats['by_degree'] ?? [])) foreach($stats['by_degree'] as $s) echo ($s->count ?? 0).','; ?>],
                            backgroundColor: '#111F35'
                        }]
                    },
                    options: { ...chartOptions, scales: { y: { beginAtZero: true } } }
                });
            }

        };

        if (document.readyState === 'complete') initCharts();
        else window.addEventListener('load', initCharts);
    })();
    </script>

</div>


<style>
@media print {
    .wp-admin #adminmenumain, .wp-admin #wpadminbar, .wp-admin #footer, .sm-btn, .no-print { display: none !important; }
    .wrap { margin: 0; padding: 0; }
    .sm-reports-container { width: 100%; }
    canvas { max-width: 300px !important; }
}
</style>
