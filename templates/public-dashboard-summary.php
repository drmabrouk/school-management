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

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
    <!-- Top Violating Students -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
        <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 15px; font-size: 1.1em;">أكثر 5 طلاب تسجيلاً للمخالفات</h3>
        <div class="sm-table-container" style="border:none;">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>عدد المخالفات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats['top_students'] ?? [])): ?>
                        <tr><td colspan="2" style="text-align:center; padding:20px;">لا يوجد بيانات كافية.</td></tr>
                    <?php else: ?>
                        <?php foreach ($stats['top_students'] as $stu): ?>
                            <tr>
                                <td style="font-weight:700; color:var(--sm-primary-color);"><?php echo esc_html($stu->name ?? '---'); ?></td>
                                <td><span class="sm-badge sm-badge-high"><?php echo esc_html($stu->count ?? 0); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Severity Breakdown -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
        <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 15px; font-size: 1.1em;">المخالفات حسب الحدة</h3>
        <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
            <?php 
            $severity_labels = SM_Settings::get_severities();
            $total = 0;
            $by_sev = $stats['by_severity'] ?? [];
            foreach($by_sev as $s) $total += $s->count;
            foreach ($by_sev as $s): 
                $perc = $total > 0 ? round(($s->count / $total) * 100) : 0;
                $color = ($s->severity ?? '') == 'high' ? '#111F35' : (($s->severity ?? '') == 'medium' ? '#D02752' : '#F63049');
            ?>
                <div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:0.9em;">
                        <strong><?php echo esc_html($severity_labels[$s->severity] ?? ($s->severity ?? '---')); ?></strong>
                        <span><?php echo esc_html($s->count ?? 0); ?> (<?php echo (int)$perc; ?>%)</span>
                    </div>
                    <div style="height:8px; background:#edf2f7; border-radius:4px; overflow:hidden;">
                        <div style="height:100%; width:<?php echo (int)$perc; ?>%; background:<?php echo esc_attr($color); ?>;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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
