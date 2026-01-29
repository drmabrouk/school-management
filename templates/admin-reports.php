<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-reports-container" dir="rtl">
    <?php
    $labels = SM_Settings::get_violation_types();
    $severity_labels = SM_Settings::get_severities();
    $all_labels = array_merge($labels, $severity_labels);
    ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-bottom: 40px;">
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
            <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">توجه السلوك العام (آخر 30 يوم)</h4>
            <canvas id="trendChart" style="max-height: 250px;"></canvas>
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

            // Trend Chart
            const trendEl = document.getElementById('trendChart');
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'line',
                    data: {
                    labels: [<?php if(!empty($stats['trends'] ?? [])) foreach($stats['trends'] as $s) echo '"'.esc_js($s->date ?? '---').'",'; ?>],
                        datasets: [{
                            label: 'النشاط اليومي',
                        data: [<?php if(!empty($stats['trends'] ?? [])) foreach($stats['trends'] as $s) echo ($s->count ?? 0).','; ?>],
                            borderColor: '#F63049',
                            backgroundColor: 'rgba(246, 48, 73, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: chartOptions
                });
            }
        };

        if (document.readyState === 'complete') initCharts();
        else window.addEventListener('load', initCharts);
    })();
    </script>

    <div style="background: var(--sm-bg-light); padding: 30px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;">
            <h3 style="margin:0; border-bottom:none; padding-bottom:0;">سجل المخالفات التفصيلي العام</h3>
            <div style="display: flex; gap: 10px;">
                <button onclick="exportToCSV()" class="sm-btn" style="width:auto; background:var(--sm-secondary-color); padding: 8px 20px;">تصدير Excel</button>
                <button onclick="window.print()" class="sm-btn" style="width:auto; background:#27ae60; padding: 8px 20px;">طباعة الكل</button>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div style="background: #fff; padding: 20px; border: 1px solid var(--sm-border-color); border-radius: 8px; margin-bottom: 25px;">
            <form method="get" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div>
                    <label class="sm-label" style="font-size: 12px;">تحديد الفترة:</label>
                    <select name="period" class="sm-select" style="width:auto;" onchange="updateDates(this.value)">
                        <option value="">فترة مخصصة</option>
                        <option value="today" <?php selected($_GET['period'] ?? '', 'today'); ?>>اليوم</option>
                        <option value="week" <?php selected($_GET['period'] ?? '', 'week'); ?>>هذا الأسبوع</option>
                        <option value="month" <?php selected($_GET['period'] ?? '', 'month'); ?>>هذا الشهر</option>
                        <option value="term" <?php selected($_GET['period'] ?? '', 'term'); ?>>الفصل الدراسي الحالي</option>
                        <option value="year" <?php selected($_GET['period'] ?? '', 'year'); ?>>هذا العام</option>
                    </select>
                </div>
                <div>
                    <label class="sm-label" style="font-size: 12px;">من تاريخ:</label>
                    <input type="date" name="start_date" id="report_start" class="sm-input" value="<?php echo esc_attr($_GET['start_date'] ?? ''); ?>" style="width:auto;">
                </div>
                <div>
                    <label class="sm-label" style="font-size: 12px;">إلى تاريخ:</label>
                    <input type="date" name="end_date" id="report_end" class="sm-input" value="<?php echo esc_attr($_GET['end_date'] ?? ''); ?>" style="width:auto;">
                </div>
                <div>
                    <label class="sm-label" style="font-size: 12px;">نوع المخالفة:</label>
                    <select name="type_filter" class="sm-select" style="width:auto;">
                        <option value="">الكل</option>
                        <?php foreach ($labels as $k => $v): ?>
                            <option value="<?php echo esc_attr($k); ?>" <?php selected($_GET['type_filter'] ?? '', $k); ?>><?php echo esc_html($v); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="sm-btn" style="width:auto; padding: 10px 25px;">تصفية النتائج</button>
                <a href="<?php echo remove_query_arg(['start_date', 'end_date', 'type_filter', 'period']); ?>" class="sm-btn" style="width:auto; background:var(--sm-text-gray); text-decoration:none; padding: 10px 20px;">إعادة ضبط</a>
            </form>
        </div>

        <script>
        function updateDates(period) {
            const start = document.getElementById('report_start');
            const end = document.getElementById('report_end');
            const today = new Date().toISOString().split('T')[0];
            
            if (period === 'today') {
                start.value = today;
                end.value = today;
            } else if (period === 'week') {
                let d = new Date();
                d.setDate(d.getDate() - 7);
                start.value = d.toISOString().split('T')[0];
                end.value = today;
            } else if (period === 'month') {
                let d = new Date();
                d.setMonth(d.getMonth() - 1);
                start.value = d.toISOString().split('T')[0];
                end.value = today;
            } else if (period === 'term') {
                start.value = '<?php echo SM_Settings::get_academic_structure()['semester_start']; ?>';
                end.value = '<?php echo SM_Settings::get_academic_structure()['semester_end']; ?>';
            } else if (period === 'year') {
                let d = new Date();
                start.value = d.getFullYear() + '-01-01';
                end.value = d.getFullYear() + '-12-31';
            }
        }
        </script>

        <div class="sm-table-container">
            <table id="reports-table" class="sm-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الطالب</th>
                        <th>الصف</th>
                        <th>النوع</th>
                        <th>الحدة</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.85em;"><?php echo esc_html($row->created_at); ?></td>
                        <td style="font-weight: 600;"><?php echo esc_html($row->student_name); ?></td>
                        <td><?php echo SM_Settings::format_grade_name($row->class_name, $row->section, 'short'); ?></td>
                        <td><?php echo isset($all_labels[$row->type]) ? $all_labels[$row->type] : esc_html($row->type); ?></td>
                        <td>
                            <span class="sm-badge sm-badge-<?php echo esc_attr($row->severity); ?>">
                                <?php echo isset($all_labels[$row->severity]) ? $all_labels[$row->severity] : esc_html($row->severity); ?>
                            </span>
                        </td>
                        <td style="font-size: 0.9em;"><?php echo esc_html($row->details); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    let csv = [];
    let rows = document.querySelectorAll("#reports-table tr");
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        csv.push(row.join(","));        
    }
    let csvFile = new Blob(["\ufeff" + csv.join("\n")], {type: "text/csv;charset=utf-8;"});
    let downloadLink = document.createElement("a");
    downloadLink.download = "school-discipline-report.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}
</script>

<style>
@media print {
    .wp-admin #adminmenumain, .wp-admin #wpadminbar, .wp-admin #footer, .sm-btn, .no-print { display: none !important; }
    .wrap { margin: 0; padding: 0; }
    .sm-reports-container { width: 100%; }
    canvas { max-width: 300px !important; }
}
</style>
