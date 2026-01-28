<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-form-container" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px;">
        <h3 class="sm-form-title" style="margin:0; border:none; padding:0; font-size: 1.2em; font-weight: 800; color: var(--sm-primary-color);">بيانات المخالفة الجديدة</h3>
        <div id="barcode-scanner-section">
            <button id="start-scanner" type="button" class="sm-btn" style="width: auto; padding: 10px 20px; background: var(--sm-dark-color); font-size: 13px; font-weight: 700;"><span class="dashicons dashicons-barcode" style="vertical-align: middle; margin-left: 5px;"></span> استخدام الماسح الضوئي</button>
        </div>
    </div>

    <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto 20px auto; display: none; border-radius: 8px; overflow: hidden; border: 2px solid var(--sm-primary-color);"></div>
    
    <div id="student-intelligence-panel" style="display:none; background: #fdfdfd; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 30px; border-right: 4px solid var(--sm-primary-color);">
        <h4 style="margin-top:0; color:var(--sm-primary-color);">تحليل سلوك الطالب الذكي</h4>
        <div id="intel-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            <!-- Content loaded via AJAX -->
        </div>
        <div id="intel-history" style="margin-top: 15px; font-size: 0.85em; color: #666; border-top: 1px dashed #eee; padding-top: 10px;">
            <!-- Latest violations -->
        </div>
    </div>

    <div id="sm-ajax-response" style="display:none; margin-bottom: 25px;"></div>

    <form method="post" id="violation-form">
        <?php wp_nonce_field('sm_record_action', 'sm_nonce'); ?>
        
        <div class="sm-form-group" style="position:relative;">
            <label class="sm-label">البحث عن الطلاب (يمكنك اختيار أكثر من طالب):</label>
            <div style="display:flex; gap:10px;">
                <input type="text" id="student_unified_search" class="sm-input" placeholder="اكتب اسم الطالب أو الكود..." autocomplete="off">
            </div>
            <div id="search_results_dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid var(--sm-border-color); border-radius:0 0 8px 8px; z-index:1000; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); max-height:250px; overflow-y:auto;">
                <!-- Results via AJAX -->
            </div>
            <div id="selected_students_container" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
                <!-- Selected students tags -->
            </div>
            <input type="hidden" name="student_ids" id="selected_student_ids" required>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div class="sm-form-group">
                <label class="sm-label">نوع المخالفة:</label>
                <select name="type" id="violation_type" class="sm-select">
                    <?php foreach (SM_Settings::get_violation_types() as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm-form-group">
                <label class="sm-label">تصنيف الموقف:</label>
                <select name="classification" class="sm-select">
                    <option value="general">عام</option>
                    <option value="inside_class">داخل الفصل</option>
                    <option value="yard">في الساحة</option>
                    <option value="labs">في المختبرات</option>
                    <option value="bus">الحافلة المدرسية</option>
                </select>
            </div>

            <div class="sm-form-group">
                <label class="sm-label">درجة الحدة:</label>
                <select name="severity" id="violation_severity" class="sm-select">
                    <option value="low">منخفضة (تنبيه)</option>
                    <option value="medium">متوسطة (إنذار)</option>
                    <option value="high">خطيرة (إجراء تأديبي)</option>
                </select>
            </div>
        </div>

        <div class="sm-form-group">
            <label class="sm-label">التفاصيل:</label>
            <textarea name="details" class="sm-textarea" placeholder="اشرح الموقف بالتفصيل..." rows="3"></textarea>
        </div>

        <div class="sm-form-group">
            <label class="sm-label">الإجراء المتخذ (اقتراحات ذكية):</label>
            <input type="text" name="action_taken" id="action_taken" class="sm-input" placeholder="مثال: تنبيه شفوي، استدعاء ولي أمر...">
            <div id="action-suggestions" style="display:flex; gap:10px; margin-top:8px; flex-wrap:wrap;">
                <!-- Suggestions based on severity -->
            </div>
        </div>

        <div class="sm-form-group">
            <label class="sm-label">المكافأة أو العقوبة الإضافية:</label>
            <input type="text" name="reward_penalty" class="sm-input" placeholder="تحسين السلوك سيؤدي إلى...">
        </div>

        <button type="submit" id="submit-btn" class="sm-btn" style="width: 100%; height: 50px; font-weight: 800; font-size: 1.1em; border-radius: 10px;">حفظ وتسجيل المخالفة الآن</button>
    </form>
</div>

<script>
(function() {
<?php $suggested = SM_Settings::get_suggested_actions(); ?>
const severityActions = {
    'low': <?php echo json_encode(explode("\n", str_replace("\r", "", $suggested['low']))); ?>,
    'medium': <?php echo json_encode(explode("\n", str_replace("\r", "", $suggested['medium']))); ?>,
    'high': <?php echo json_encode(explode("\n", str_replace("\r", "", $suggested['high']))); ?>
};

const sevEl = document.getElementById('violation_severity');
if (sevEl) {
    sevEl.addEventListener('change', function() {
        updateSuggestions(this.value);
    });
}

function updateSuggestions(sev) {
    const container = document.getElementById('action-suggestions');
    if (!container) return;
    container.innerHTML = '';
    if (severityActions[sev]) {
        severityActions[sev].forEach(act => {
            const btn = document.createElement('span');
            btn.innerText = act;
            btn.style = "cursor:pointer; background:#edf2f7; padding:4px 10px; border-radius:4px; font-size:12px; border:1px solid #cbd5e0;";
            btn.onclick = () => {
                const input = document.getElementById('action_taken');
                if (input) input.value = act;
            };
            container.appendChild(btn);
        });
    }
}

updateSuggestions('low');

let searchTimer;
document.addEventListener('click', function(e) {
    const searchInput = document.getElementById('student_unified_search');
    if (searchInput && !searchInput.contains(e.target)) {
        const dropdown = document.getElementById('search_results_dropdown');
        if (dropdown) dropdown.style.display = 'none';
    }
});

document.getElementById('student_unified_search').addEventListener('input', function() {
    const query = this.value;
    clearTimeout(searchTimer);
    if (query.length < 2) {
        document.getElementById('search_results_dropdown').style.display = 'none';
        return;
    }

    searchTimer = setTimeout(() => {
        const formData = new FormData();
        formData.append('action', 'sm_search_students');
        formData.append('query', query);

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const results = res.data;
                const dropdown = document.getElementById('search_results_dropdown');
                dropdown.innerHTML = '';
                if (results.length === 0) {
                    dropdown.innerHTML = '<div style="padding:10px; color:#666; text-align:center;">لم يتم العثور على نتائج.</div>';
                } else {
                    results.forEach(s => {
                        const div = document.createElement('div');
                        div.className = 'sm-search-result-item';
                        div.style = "padding:12px 15px; border-bottom:1px solid #eee; cursor:pointer; display:flex; align-items:center; gap:10px; transition: background 0.2s;";
                        div.onmouseover = () => div.style.background = '#f8fafc';
                        div.onmouseout = () => div.style.background = '#fff';
                        div.innerHTML = `
                            ${s.photo_url ? `<img src="${s.photo_url}" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">` : '<span class="dashicons dashicons-admin-users"></span>'}
                            <div>
                                <div style="font-weight:700;">${s.name}</div>
                                <div style="font-size:11px; color:#666;">كود: ${s.student_code} | فصل: ${s.class_name}</div>
                            </div>
                        `;
                        div.onclick = () => selectStudent(s);
                        dropdown.appendChild(div);
                    });
                }
                dropdown.style.display = 'block';
            }
        });
    }, 300);
});

let selectedStudents = [];

function selectStudent(s) {
    if (selectedStudents.find(x => x.id === s.id)) return;
    
    selectedStudents.push(s);
    renderSelectedStudents();
    document.getElementById('student_unified_search').value = '';
    document.getElementById('search_results_dropdown').style.display = 'none';
    
    if (selectedStudents.length === 1) {
        fetchIntelligence(s.id);
    } else {
        document.getElementById('student-intelligence-panel').style.display = 'none';
    }
}

function renderSelectedStudents() {
    const container = document.getElementById('selected_students_container');
    container.innerHTML = '';
    const ids = [];
    
    selectedStudents.forEach(s => {
        ids.push(s.id);
        const tag = document.createElement('div');
        tag.style = "background:#f0f7ff; padding:5px 12px; border-radius:20px; border:1px solid #c3dafe; display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--sm-primary-color);";
        tag.innerHTML = `
            <span>${s.name}</span>
            <span onclick="removeStudent(${s.id})" style="cursor:pointer; color:#e53e3e;">✖</span>
        `;
        container.appendChild(tag);
    });
    
    document.getElementById('selected_student_ids').value = ids.join(',');
}

function removeStudent(id) {
    selectedStudents = selectedStudents.filter(x => x.id !== id);
    renderSelectedStudents();
    if (selectedStudents.length === 1) fetchIntelligence(selectedStudents[0].id);
    else document.getElementById('student-intelligence-panel').style.display = 'none';
}

function clearStudentSelection() {
    selectedStudents = [];
    renderSelectedStudents();
    document.getElementById('student-intelligence-panel').style.display = 'none';
}

document.getElementById('start-scanner').addEventListener('click', function() {
    const reader = document.getElementById('reader');
    reader.style.display = 'block';
    const html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({ facingMode: "environment" }, { fps: 15, qrbox: 250 }, onScanSuccess);

    function onScanSuccess(decodedText) {
        html5QrCode.stop().then(() => {
            reader.style.display = 'none';
            
            const formData = new FormData();
            formData.append('action', 'sm_get_student');
            formData.append('code', decodedText);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    selectStudent(res.data);
                } else {
                    alert('عذراً، كود غير معروف: ' + decodedText);
                }
            });
        });
    }
});

function fetchIntelligence(studentId) {
    if (!studentId) {
        document.getElementById('student-intelligence-panel').style.display = 'none';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'sm_get_student_intelligence');
    formData.append('student_id', studentId);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const data = res.data;
            document.getElementById('student-intelligence-panel').style.display = 'block';
            
            let photoHtml = data.photo_url ? `<img src="${data.photo_url}" style="width:60px; height:60px; border-radius:50%; object-fit:cover; margin-bottom:10px; border:2px solid var(--sm-primary-color);">` : '';

            let intelHtml = `
                <div style="grid-column: 1 / -1; display:flex; align-items:center; gap:15px; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:10px;">
                    ${photoHtml}
                    <h4 style="margin:0;">تحليل بيانات الطالب</h4>
                </div>
                <div><strong>إجمالي المخالفات:</strong> <span style="color:red;">${data.stats.total}</span></div>
                <div><strong>النوع الأكثر تكراراً:</strong> <span>${data.labels[data.stats.frequent_type] || 'لا يوجد'}</span></div>
                <div><strong>آخر إجراء متخذ:</strong> <span>${data.stats.last_action || 'لا يوجد'}</span></div>
            `;
            document.getElementById('intel-content').innerHTML = intelHtml;

            let historyHtml = '<strong>آخر 3 ملاحظات:</strong> ';
            if (data.recent.length === 0) historyHtml += 'لا يوجد سجل سابق.';
            data.recent.forEach(r => {
                historyHtml += `<span style="margin-left:15px;">• ${r.created_at.split(' ')[0]}: ${data.labels[r.type]} (${r.severity})</span>`;
            });
            document.getElementById('intel-history').innerHTML = historyHtml;

            // Smart Auto-select based on history
            if (data.stats.high_severity_count > 2) {
                document.getElementById('violation_severity').value = 'high';
                updateSuggestions('high');
            }
        }
    });
}

// Handle Form Submission via AJAX
document.getElementById('violation-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submit-btn');
    const responseDiv = document.getElementById('sm-ajax-response');
    
    btn.innerText = 'جاري الحفظ...';
    btn.disabled = true;

    const formData = new FormData(this);
    formData.append('action', 'sm_save_record_ajax');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const printUrl = res.data.print_url;
            const waMsg = encodeURIComponent(`تحية طيبة وبعد،\nنود إفادتكم بأنه تم تسجيل ملاحظة سلوكية بحق الطالب المذكور تفاصيلها كالتالي:\n\nالمخالفة: ${formData.get('details')}\nالإجراء: ${formData.get('action_taken')}\n\nشاكرين تعاونكم معنا لمصلحة الطالب.\nإدارة المدرسة`);
            
            responseDiv.innerHTML = `
                <div style="background: #f0fff4; color: #22543d; padding: 25px; border-radius: 12px; border: 1px solid #c6f6d5; font-weight: 600;">
                    <div style="margin-bottom: 15px;">✅ تم حفظ السجل بنجاح وإرسال التنبيهات اللازمة.</div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="${printUrl}" target="_blank" class="sm-btn" style="width:auto; padding: 8px 15px; font-size:13px; background:#38a169;">🖨️ طباعة الإشعار</a>
                        <a href="https://wa.me/?text=${waMsg}" target="_blank" class="sm-btn" style="width:auto; padding: 8px 15px; font-size:13px; background:#25D366; border:none;">📱 مشاركة عبر واتساب</a>
                    </div>
                </div>
            `;
            responseDiv.style.display = 'block';
            this.reset();
            clearStudentSelection();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            smShowNotification('تم تسجيل المخالفة بنجاح');
        } else {
            smShowNotification('خطأ: ' + (res.data || 'فشل في حفظ السجل'), true);
        }
        btn.innerText = 'حفظ وإرسال تنبيه فوري';
        btn.disabled = false;
    });
});
})();
</script>
