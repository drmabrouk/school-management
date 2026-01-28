<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-content-wrapper" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="margin:0; border:none; padding:0;">إدارة شؤون أولياء الأمور</h3>
        <?php if (current_user_can('إدارة_أولياء_الأمور')): ?>
            <button onclick="document.getElementById('add-parent-modal').style.display='flex'" class="sm-btn" style="width:auto;">+ إضافة ولي أمر جديد</button>
        <?php endif; ?>
    </div>

    <div style="background: var(--sm-bg-light); padding: 25px; border: 1px solid var(--sm-border-color); border-radius: 8px; margin-bottom: 30px;">
        <form method="get" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <label class="sm-label">بحث عن ولي أمر (بالاسم أو البريد):</label>
                <input type="text" name="parent_search" class="sm-input" value="<?php echo esc_attr(isset($_GET['parent_search']) ? $_GET['parent_search'] : ''); ?>" placeholder="أدخل بيانات ولي الأمر...">
            </div>
            <div style="display: flex; gap: 10px; align-self: flex-end;">
                <button type="submit" class="sm-btn" style="width:auto;">بحث</button>
                <a href="<?php echo remove_query_arg('parent_search'); ?>" class="sm-btn" style="width:auto; background:var(--sm-text-gray); text-decoration:none;">إعادة ضبط</a>
            </div>
        </form>
    </div>

    <div class="sm-parents-rows-container" style="display: flex; flex-direction: column; gap: 15px;">
        <?php 
        $args = array('role' => 'sm_parent');
        if (!empty($_GET['parent_search'])) {
            $args['search'] = '*' . esc_attr($_GET['parent_search']) . '*';
            $args['search_columns'] = array('user_login', 'display_name', 'user_email');
        }
        $parents = get_users($args);
        if (empty($parents)): ?>
            <div style="padding: 60px; text-align: center; background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); color: #a0aec0;">
                <span class="dashicons dashicons-admin-users" style="font-size: 48px; width:48px; height:48px; margin-bottom:15px;"></span>
                <p>لا يوجد أولياء أمور مسجلون حالياً.</p>
            </div>
        <?php else: ?>
            <?php foreach ($parents as $parent): 
                $children = SM_DB::get_students_by_parent($parent->ID);
            ?>
                <div class="sm-parent-row" style="background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); padding: 20px 30px; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 20px; flex: 2;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #f0f4f8; display: flex; align-items: center; justify-content: center; font-size: 20px;">👨‍👩‍👧</div>
                        <div>
                            <div style="font-weight: 800; color: var(--sm-secondary-color); font-size: 1.1em;"><?php echo esc_html($parent->display_name); ?></div>
                            <div style="font-size: 0.85em; color: #718096; margin-top: 3px;"><?php echo esc_html($parent->user_email); ?></div>
                        </div>
                    </div>

                    <div style="flex: 2; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px solid #edf2f7; font-size: 0.9em;">
                        <strong>الأبناء:</strong> 
                        <?php if (empty($children)): ?>
                            <span style="color: #e53e3e; font-size: 12px; margin-right: 10px;">لا يوجد أبناء مرتبطين</span>
                        <?php else: ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px;">
                                <?php foreach ($children as $c): ?>
                                    <span class="sm-badge sm-badge-low" style="background: #fff; font-size: 11px;"><?php echo esc_html($c->name); ?> (<?php echo esc_html($c->class_name); ?>)</span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="flex: 1; display: flex; gap: 10px; justify-content: flex-end;">
                        <button onclick="requestCallIn(<?php echo $parent->ID; ?>, '<?php echo esc_js($parent->display_name); ?>')" class="sm-action-btn-row" style="background: #ebf8ff; color: #3182ce; border:none; padding: 8px 15px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 12px; white-space: nowrap;">
                            <span class="dashicons dashicons-email-alt" style="font-size:14px; margin-left:5px;"></span> طلب لقاء
                        </button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('حذف حساب ولي الأمر؟')">
                            <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
                            <input type="hidden" name="delete_user_id" value="<?php echo $parent->ID; ?>">
                            <button type="submit" name="sm_delete_user" class="sm-icon-btn-row" style="color: #e53e3e;" title="حذف الحساب"><span class="dashicons dashicons-trash"></span></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal Template Wrapper -->
    <style>
    .sm-modal-overlay {
        display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(5px); z-index:10000; align-items:center; justify-content:center;
    }
    .sm-modal-content {
        background:white; padding:40px; border-radius:20px; max-width:550px; width:95%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); position:relative;
    }
    .sm-modal-close {
        position:absolute; top:20px; left:20px; background:#f7fafc; border:1px solid #e2e8f0; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:20px; color:#4a5568; transition:0.2s;
    }
    .sm-modal-close:hover { background:#edf2f7; color:#e53e3e; }
    </style>

    <div id="add-parent-modal" class="sm-modal-overlay">
        <div class="sm-modal-content">
            <button class="sm-modal-close" onclick="document.getElementById('add-parent-modal').style.display='none'">&times;</button>
            <h3 style="margin:0 0 25px 0; border-bottom:1px solid #eee; padding-bottom:15px;">إضافة ولي أمر جديد</h3>
            <form id="add-parent-form">
                <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
                <input type="hidden" name="user_role" value="sm_parent">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل:</label>
                        <input type="text" name="display_name" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">اسم المستخدم (Login):</label>
                        <input type="text" name="user_login" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">البريد الإلكتروني:</label>
                        <input type="email" name="user_email" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">كلمة المرور:</label>
                        <input type="password" name="user_pass" class="sm-input" required>
                    </div>
                </div>
                <p style="font-size:12px; color:#718096; margin-top:15px;">ملاحظة: لربط ولي الأمر بطالب، قم بتحرير بيانات الطالب من قسم "إدارة الطلاب".</p>
                <button type="submit" class="sm-btn" style="margin-top:20px;">إنشاء الحساب الآن</button>
            </form>
        </div>
    </div>

    <script>
    (function() {
        const addForm = document.getElementById('add-parent-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_add_parent_ajax');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تمت إضافة ولي الأمر');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            });
        }
    })();
    </script>
    <div id="call-in-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:white; padding:40px; border-radius:12px; max-width:500px; width:90%; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <h3 style="margin:0; border:none; padding:0;">إرسال طلب استدعاء ولي أمر</h3>
                <button onclick="document.getElementById('call-in-modal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            <form method="post">
                <?php wp_nonce_field('sm_message_action', 'sm_nonce'); ?>
                <input type="hidden" name="receiver_id" id="call_in_parent_id">
                <p>إرسال طلب حضور للمدرسة لولي الأمر: <strong id="call_in_parent_name"></strong></p>
                <div class="sm-form-group">
                    <label class="sm-label">رسالة إضافية أو سبب الاستدعاء:</label>
                    <textarea name="message" class="sm-textarea" rows="4" required>نرجو منكم التكرم بزيارة مكتب الإرشاد الطلابي بالمدرسة في أقرب وقت ممكن لمناقشة أمور هامة تخص ابنكم.</textarea>
                </div>
                <button type="submit" name="sm_send_call_in" class="sm-btn">إرسال الطلب الآن</button>
            </form>
        </div>
    </div>

    <script>
    function requestCallIn(id, name) {
        document.getElementById('call_in_parent_id').value = id;
        document.getElementById('call_in_parent_name').innerText = name;
        document.getElementById('call-in-modal').style.display = 'flex';
    }
    </script>
</div>
