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

<?php 
$current_user = wp_get_current_user();
$is_power_admin = in_array('administrator', (array)$current_user->roles) || in_array('sm_school_admin', (array)$current_user->roles);
if ($is_power_admin): ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
    <!-- Teachers Complaints -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #fed7d7; border-right: 5px solid #e53e3e; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <h3 style="margin-top:0; border-bottom: 1px solid #feb2b2; padding-bottom: 15px; color: #c53030; display: flex; align-items: center; gap: 10px; font-size: 1.1em;">
            <span class="dashicons dashicons-warning"></span> بلاغات المعلمين (قيد المراجعة)
        </h3>
        <div class="sm-table-container" style="border:none;">
            <table class="sm-table" style="font-size: 0.85em;">
                <thead>
                    <tr>
                        <th>المعلم</th>
                        <th>الطالب</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $pending_records = SM_DB::get_records(array('status' => 'pending'));
                    if (empty($pending_records)): ?>
                        <tr><td colspan="3" style="text-align:center; padding:20px; color:#666;">لا توجد بلاغات معلقة.</td></tr>
                    <?php else: 
                        foreach (array_slice($pending_records, 0, 4) as $pr): 
                            $teacher = get_userdata($pr->teacher_id);
                        ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo $teacher ? esc_html($teacher->display_name) : '---'; ?></td>
                                <td><?php echo esc_html($pr->student_name); ?></td>
                                <td><a href="<?php echo add_query_arg('sm_tab', 'stats'); ?>" class="sm-btn" style="padding:4px 8px; font-size:10px; width:auto; background:#c53030; text-decoration:none;">مراجعة</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Discipline Officer Updates -->
    <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #bee3f8; border-right: 5px solid #3182ce; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <h3 style="margin-top:0; border-bottom: 1px solid #bee3f8; padding-bottom: 15px; color: #2b6cb0; display: flex; align-items: center; gap: 10px; font-size: 1.1em;">
            <span class="dashicons dashicons-id-alt"></span> آخر تحديثات مسؤولي الانضباط
        </h3>
        <div class="sm-table-container" style="border:none;">
            <table class="sm-table" style="font-size: 0.85em;">
                <thead>
                    <tr>
                        <th>المسؤول</th>
                        <th>النشاط</th>
                        <th>الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $officer_logs = array_filter(SM_Logger::get_logs(20), function($log) {
                        $user = get_userdata($log->user_id);
                        return $user && in_array('sm_discipline_officer', (array)$user->roles);
                    });
                    if (empty($officer_logs)): ?>
                        <tr><td colspan="3" style="text-align:center; padding:20px; color:#666;">لا توجد تحديثات أخيرة.</td></tr>
                    <?php else: 
                        foreach (array_slice($officer_logs, 0, 4) as $log): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo esc_html($log->display_name); ?></td>
                                <td><?php echo esc_html($log->action); ?></td>
                                <td style="color:#718096;"><?php echo date('H:i', strtotime($log->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div style="background: var(--sm-bg-light); padding: 30px; border-radius: 12px; border: 1px solid var(--sm-border-color);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="margin:0; border:none;">سجل المخالفات العام (الأحدث أولاً)</h3>
        <a href="<?php echo add_query_arg('sm_tab', 'reports'); ?>" class="sm-btn" style="width:auto; font-size:12px; background:var(--sm-secondary-color); text-decoration:none;">التقارير التحليلية</a>
    </div>
    <?php 
    $recent_records = SM_DB::get_records(['limit' => 10]); // I should check if get_records supports limit, if not I'll slice
    if (empty($recent_records)): ?>
        <p style="text-align: center; color: var(--sm-text-gray); padding: 20px;">لا توجد مخالفات مسجلة حالياً.</p>
    <?php else: 
        $all_labels = SM_Settings::get_violation_types();
        $severity_labels = SM_Settings::get_severities();
    ?>
        <div class="sm-table-container">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الطالب</th>
                        <th>النوع</th>
                        <th>الحدة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($recent_records, 0, 10) as $row): ?>
                        <tr>
                            <td style="font-size: 0.85em;"><?php echo date('Y-m-d', strtotime($row->created_at)); ?></td>
                            <td style="font-weight: 600;"><?php echo esc_html($row->student_name); ?></td>
                            <td><?php echo $all_labels[$row->type] ?? $row->type; ?></td>
                            <td><span class="sm-badge sm-badge-<?php echo esc_attr($row->severity); ?>"><?php echo $severity_labels[$row->severity] ?? $row->severity; ?></span></td>
                            <td><?php echo esc_html($row->action_taken); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
