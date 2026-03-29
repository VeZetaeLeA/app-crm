<div class="row g-4">
    <!-- Ticket Info & Metadata -->
    <div class="col-lg-4">
        <div class="glass-morphism p-4 rounded-5 border-white-10 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <span class="badge border border-white-10 text-white-50 px-3 py-2 uppercase x-small">
                    <?php echo $ticket['ticket_number']; ?>
                </span>
                <?php
                $statusClassDetail = [
                    'open' => 'bg-danger-subtle',
                    'in_analysis' => 'bg-warning-subtle',
                    'budget_sent' => 'bg-info-subtle',
                    'budget_approved' => 'bg-success-subtle',
                    'budget_rejected' => 'bg-danger-subtle',
                    'invoiced' => 'bg-info-subtle',
                    'payment_pending' => 'bg-warning-subtle',
                    'active' => 'bg-success-subtle',
                    'resolved' => 'bg-success-subtle',
                    'closed' => 'bg-secondary-subtle',
                    'void' => 'bg-dark text-white'
                ];
                $detailCls = $statusClassDetail[$ticket['status']] ?? 'bg-primary bg-opacity-10 text-primary';
                ?>
                <span class="badge <?php echo $detailCls; ?> px-3 py-2 uppercase x-small fw-black">
                    <?php echo translateStatus($ticket['status']); ?>
                </span>
            </div>

            <?php if (isset($ticket['ai_sentiment']) && !\Core\Auth::isClient()): ?>
                <div class="mb-4 d-flex align-items-center gap-2 bg-white-5 p-2 rounded-4 border border-info border-opacity-10">
                    <?php 
                    $sentIcons = [
                        'happy' => ['icon' => 'sentiment_satisfied', 'class' => 'text-success', 'label' => __('tickets.sentiment_positive')],
                        'neutral' => ['icon' => 'sentiment_neutral', 'class' => 'text-info', 'label' => __('tickets.sentiment_neutral')],
                        'angry' => ['icon' => 'sentiment_very_dissatisfied', 'class' => 'text-danger', 'label' => __('tickets.sentiment_angry')],
                        'urgent' => ['icon' => 'notification_important', 'class' => 'text-warning', 'label' => __('tickets.urgency_detected')]
                    ];
                    $sent = $sentIcons[$ticket['ai_sentiment']] ?? $sentIcons['neutral'];
                    ?>
                    <span class="material-symbols-outlined <?php echo $sent['class']; ?> fs-5"><?php echo $sent['icon']; ?></span>
                    <span class="text-white-50 x-small uppercase fw-bold"><?php echo $sent['label']; ?></span>
                </div>
            <?php endif; ?>

            <h2 class="text-white h5 fw-black mb-1">
                <?php echo $ticket['subject']; ?>
                </h3>
                <p class="text-white-50 x-small uppercase tracking-widest mb-4"><?= __('tickets.requested_on') ?>
                    <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                </p>

                <hr class="border-white-10 my-4">

                <div class="space-y-4">
                    <div class="mb-3">
                        <label
                            class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-1"><?= __('tickets.client') ?></label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-steel d-flex align-items-center justify-content-center text-accent fw-bold x-small"
                                style="width: 32px; height: 32px;">
                                <?php echo strtoupper(substr($ticket['client_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-white small fw-bold mb-0">
                                    <?php echo $ticket['client_name']; ?>
                                </p>
                                <p class="text-white-50 x-small mb-0">
                                    <?php echo $ticket['client_company']; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-1"><?= __('tickets.requested_service') ?></label>
                        <p class="text-white small mb-1">
                            <?php echo $ticket['service_name']; ?>
                        </p>
                        <span class="badge border border-primary text-primary x-small px-2 py-1">
                            <?php echo $ticket['plan_name']; ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <label
                            class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-1"><?= __('tickets.description') ?></label>
                        <div class="bg-white-5 p-3 rounded-4">
                            <p class="text-white-50 small mb-0">
                                <?php echo nl2br($ticket['description']); ?>
                            </p>
                        </div>
                    </div>

                    <?php if (!empty($tasks)): ?>
                        <div class="mb-3">
                            <label class="text-info x-small uppercase fw-bold tracking-widest d-block mb-2"><?= __('tickets.action_items') ?></label>
                            <div class="space-y-2">
                                <?php foreach ($tasks as $task): ?>
                                    <div class="d-flex align-items-center gap-2 bg-white-5 p-2 rounded-3 border border-white-5">
                                        <span class="material-symbols-outlined text-info fs-6">task_alt</span>
                                        <span class="text-white-50 x-small"><?php echo $task['description']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!\Core\Auth::isClient()): ?>
                    <hr class="border-white-10 my-4">
                    <div class="mb-4">
                        <label
                            class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-2"><?= __('tickets.budget') ?></label>
                        <?php if ($budget): ?>
                            <a href="<?php echo url('budget/show/' . $budget['id']); ?>"
                                class="btn btn-outline-primary btn-sm w-100 fw-bold py-2 mb-2">
                                <?= __('tickets.view_budget') ?> (<?php echo strtoupper($budget['status']); ?>)
                            </a>
                            <?php if (isset($invoice) && $invoice): ?>
                                <a href="<?php echo url('invoice/show/' . $invoice['id']); ?>"
                                    class="btn btn-outline-success btn-sm w-100 fw-bold py-2 mb-2 d-flex align-items-center justify-content-center gap-2">
                                    <span class="material-symbols-outlined fs-6">receipt_long</span>
                                    <?= __('tickets.view_invoice') ?> (<?php echo strtoupper(translateStatus($invoice['status'])); ?>)
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo url('budget/create/' . $ticket['id']); ?>"
                                class="btn btn-primary btn-sm w-100 fw-bold py-2 mb-2 shadow-gold">
                                <?= __('tickets.generate_budget') ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <hr class="border-white-10 my-4">
                    <div class="mb-4">
                        <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-3"><?= __('tickets.ai_copilot') ?></label>
                        <button type="button" id="btn-generate-summary" class="btn btn-outline-info btn-sm w-100 fw-bold py-2 mb-3 d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined fs-6">auto_awesome</span>
                            <?= __('tickets.generate_ai_summary') ?>
                        </button>
                        <div id="ai-summary-container" class="bg-info bg-opacity-10 border border-info border-opacity-25 p-3 rounded-4 d-none">
                            <p class="text-info x-small uppercase fw-black mb-2 tracking-widest"><?= __('tickets.case_summary') ?></p>
                            <div id="ai-summary-content" class="text-white-50 small" style="line-height: 1.6;"></div>
                        </div>
                    </div>

                    <form action="<?php echo url('ticket/updateStatus'); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                        <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-2"><?= __('tickets.manage_status') ?></label>
                        <div class="d-flex gap-2">
                            <select name="status" class="form-select form-select-sm bg-steel border-white-10 text-white">
                                <?php foreach (\App\Domain\Ticket\TicketStatus::all() as $ts): ?>
                                    <option value="<?php echo $ts; ?>" <?php echo $ticket['status'] == $ts ? 'selected' : ''; ?>>
                                        <?php echo \App\Domain\Ticket\TicketStatus::fromString($ts)->getLabel(); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">OK</button>
                        </div>
                    </form>
                <?php else: ?>
                    <?php if ($budget): ?>
                        <hr class="border-white-10 my-4">
                        <div class="mb-4">
                            <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-2"><?= __('tickets.commercial_proposal') ?></label>
                            <p class="text-white-50 x-small mb-3"><?= __('tickets.proposal_received') ?></p>
                            <a href="<?php echo url('budget/show/' . $budget['id']); ?>"
                                class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-gold">
                                <span class="material-symbols-outlined fs-6 align-middle me-1">visibility</span> <?= __('tickets.review_proposal') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
        </div>
    </div>

    <!-- Chat Interface -->
    <div class="col-lg-8">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden d-flex flex-column"
            style="height: calc(100vh - 200px);">
            <div
                class="p-3 border-bottom border-white-10 bg-white-5 d-flex align-items-center justify-content-between px-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="material-symbols-outlined text-primary">forum</span>
                    <h2 class="text-white h6 mb-0 fw-black"><?= __('tickets.direct_communication') ?></h2>
                </div>
                <span class="x-small text-white-50 uppercase tracking-widest fw-bold"><?= __('tickets.real_time') ?></span>
            </div>

            <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column gap-3" id="chat-container">
                <?php if (empty($messages)): ?>
                    <div class="mt-auto mb-auto text-center py-5">
                        <span class="material-symbols-outlined display-1 text-white-10 mb-3">chat_bubble</span>
                        <p class="text-white-50"><?= __('tickets.start_conversation') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php if ($msg['message_type'] === 'system' || empty($msg['user_id'])): ?>
                            <div class="align-self-center text-center w-100 my-2">
                                <div class="d-inline-block px-4 py-3 rounded-4"
                                    style="background: linear-gradient(135deg, rgba(212,175,55,0.12), rgba(48,197,255,0.08)); border: 1px solid rgba(212,175,55,0.25); max-width: 90%; text-align: left;">
                                    <p class="x-small mb-0 text-white" style="white-space: pre-line; line-height: 1.7;">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <span class="material-symbols-outlined text-primary"
                                            style="font-size: 12px;">smart_toy</span>
                                        <span class="x-small text-white-50"><?php echo \Core\Config::get('business.company_name'); ?> <?= __('common.search') == 'Search' ? 'Bot' : 'Bot' ?> •
                                            <?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php
                            $isMe = ($msg['user_id'] == \Core\Auth::user()['id']);
                            $alignClass = $isMe ? 'align-self-end' : 'align-self-start';
                            $bgClass = $isMe ? 'bg-primary text-deep-black shadow-gold' : 'bg-steel text-white';
                            $radiusClass = $isMe ? 'rounded-start-4 rounded-top-4' : 'rounded-end-4 rounded-top-4';
                            ?>
                            <div class="d-flex flex-column <?php echo $alignClass; ?>" style="max-width: 80%;">
                                <div class="p-3 mb-1 <?php echo $bgClass; ?> <?php echo $radiusClass; ?>">
                                    <p class="small mb-0">
                                        <?php echo nl2br($msg['message']); ?>
                                    </p>
                                </div>
                                <span class="x-small text-white-50 <?php echo $isMe ? 'text-end' : 'text-start'; ?>">
                                    <?php echo !$isMe ? "<strong>{$msg['user_name']}</strong> • " : ""; ?>
                                    <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="p-3 border-top border-white-10 bg-deep-black bg-opacity-30">
                <form action="<?php echo url('chat/send'); ?>" method="POST" class="d-flex gap-2 align-items-center">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                    <div class="position-relative flex-grow-1">
                        <textarea name="message" id="chat-input" class="form-control bg-steel border-white-10 text-white p-3 pe-5"
                             placeholder="<?= __('tickets.write_message') ?>" required autocomplete="off" rows="1" style="resize: none; min-height: 58px; max-height: 200px; overflow-y: hidden;"></textarea>
                        <div class="position-absolute top-50 end-0 translate-middle-y d-flex gap-1 me-2">
                            <button type="button" id="btn-ai-suggest" class="btn text-info p-1" title="<?= __('tickets.ai_suggest_response') ?>">
                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">lightbulb</span>
                            </button>
                            <button type="button" id="btn-ai-rewrite" class="btn text-primary p-1" title="<?= __('tickets.ai_improve_tone') ?>">
                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">magic_button</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4 py-3 d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Scroll to bottom of chat
    const chatContainer = document.getElementById('chat-container');
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Auto-refresh chat every 5 seconds
    setInterval(() => {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newChat = doc.getElementById('chat-container').innerHTML;
                const oldChat = document.getElementById('chat-container');

                if (oldChat && oldChat.innerHTML !== newChat) {
                    const isAtBottom = oldChat.scrollTop + oldChat.clientHeight >= oldChat.scrollHeight - 50;
                    oldChat.innerHTML = newChat;
                    if (isAtBottom) {
                        oldChat.scrollTop = oldChat.scrollHeight;
                    }
                }
            })
            .catch(err => console.error('Error refreshing chat:', err));
    }, 5000);
    // AI Summary logic
    const btnSummary = document.getElementById('btn-generate-summary');
    const summaryContainer = document.getElementById('ai-summary-container');
    const summaryContent = document.getElementById('ai-summary-content');

    if (btnSummary) {
        btnSummary.addEventListener('click', async () => {
            const originalHtml = btnSummary.innerHTML;
            btnSummary.disabled = true;
            btnSummary.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + '<?= __('tickets.processing') ?>';

            try {
                const response = await fetch('<?php echo url("AI/generateSummary/" . $ticket["id"]); ?>');
                const data = await response.json();

                if (data.success) {
                    summaryContent.innerHTML = data.summary.replace(/\n/g, '<br>');
                    summaryContainer.classList.remove('d-none');
                    btnSummary.classList.add('d-none'); // Hide button after success
                } else {
                    alert(data.error || '<?= __('tickets.ai_error') ?>');
                }
            } catch (error) {
                console.error('AI Error:', error);
                alert('<?= __('tickets.ai_connection_error') ?>');
            } finally {
                btnSummary.disabled = false;
                btnSummary.innerHTML = originalHtml;
            }
        });
    }

    // AI Rewrite logic
    const btnRewrite = document.getElementById('btn-ai-rewrite');
    const chatInput = document.getElementById('chat-input');

    if (btnRewrite) {
        btnRewrite.addEventListener('click', async () => {
            const draft = chatInput.value.trim();
            if (!draft) return;

            const originalIcon = btnRewrite.innerHTML;
            btnRewrite.classList.add('spinning');
            btnRewrite.disabled = true;

            try {
                const response = await fetch('<?php echo url("AI/rewriteDraft"); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ draft: draft })
                });
                const data = await response.json();

                if (data.success) {
                    chatInput.value = data.rewritten;
                    chatInput.dispatchEvent(new Event('input')); // Trigger resize
                } else {
                    alert(data.error || 'No se pudo mejorar el tono del mensaje.');
                }
            } catch (error) {
                console.error('Copilot Error:', error);
                alert('Ocurrió un error al contactar con el Copilot.');
            } finally {
                btnRewrite.classList.remove('spinning');
                btnRewrite.disabled = false;
                btnRewrite.innerHTML = originalIcon;
            }
        });
    }

    // AI Suggestion logic (GAI-05)
    const btnSuggest = document.getElementById('btn-ai-suggest');
    if (btnSuggest) {
        btnSuggest.addEventListener('click', async () => {
            const originalIcon = btnSuggest.innerHTML;
            btnSuggest.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btnSuggest.disabled = true;

            try {
                const response = await fetch('<?php echo url("AI/suggestResponse/" . $ticket["id"]); ?>');
                const data = await response.json();

                if (data.success) {
                    chatInput.value = data.suggestion;
                    // Trigger adjustment if there's any auto-resize logic
                } else {
                    alert(data.error);
                }
            } catch (error) {
                console.error('AI Suggest Error:', error);
            } finally {
                btnSuggest.innerHTML = originalIcon;
                btnSuggest.disabled = false;
            }
        });
    }
    // Auto-resize textarea
    if (chatInput) {
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (this.scrollHeight > 200) {
                this.style.overflowY = 'auto';
            } else {
                this.style.overflowY = 'hidden';
            }
        });
    }
</script>

<style>
@keyframes spin {
    from { transform: translateY(-50%) rotate(0deg); }
    to { transform: translateY(-50%) rotate(360deg); }
}
.spinning span {
    display: inline-block;
    animation: spin 1s linear infinite;
}
</style>