<div class="row g-4">
    <!-- Ticket Info & Metadata -->
    <div class="col-lg-4">
        <div class="glass-morphism p-4 rounded-5 border-white-10 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <span class="badge border border-white-10 text-white-50 px-3 py-2 uppercase x-small">
                    <?php echo $ticket['ticket_number']; ?>
                </span>
                <?php
                $statusClassDetailClient = [
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
                $detailCls = $statusClassDetailClient[$ticket['status']] ?? 'bg-primary bg-opacity-10 text-primary';
                ?>
                <span class="badge <?php echo $detailCls; ?> px-3 py-2 uppercase x-small fw-black">
                    <?php echo translateStatus($ticket['status']); ?>
                </span>
            </div>

            <h3 class="text-white h5 fw-bold mb-1">
                <?php echo $ticket['subject']; ?>
            </h3>
            <p class="text-white-50 x-small uppercase tracking-widest mb-4"><?= __('tickets.requested_on') ?>
                <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
            </p>

            <hr class="border-white-10 my-4">

            <div class="space-y-4">
                <div class="mb-3">
                    <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-1"><?= __('tickets.client') ?></label>
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
            </div>

            <?php if (!\Core\Auth::isClient()): ?>
                <hr class="border-white-10 my-4">
                <div class="mb-4">
                    <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-2">Presupuesto</label>
                    <?php if ($budget): ?>
                        <a href="<?php echo url('budget/show/' . $budget['id']); ?>"
                            class="btn btn-outline-primary btn-sm w-100 fw-bold py-2 mb-2">
                            Ver Presupuesto (<?php echo strtoupper($budget['status']); ?>)
                        </a>
                    <?php else: ?>
                        <a href="<?php echo url('budget/create/' . $ticket['id']); ?>"
                            class="btn btn-primary btn-sm w-100 fw-bold py-2 mb-2 shadow-gold">
                            Generar Presupuesto
                        </a>
                    <?php endif; ?>
                </div>

                <form action="<?php echo url('ticket/updateStatus'); ?>" method="POST">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                    <label class="text-white-50 x-small uppercase fw-bold tracking-widest d-block mb-2">Gestionar
                        Estado</label>
                    <div class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm bg-steel border-white-10 text-white">
                            <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Abierto</option>
                            <option value="in_analysis" <?php echo $ticket['status'] == 'in_analysis' ? 'selected' : ''; ?>>En
                                Análisis</option>
                            <option value="budget_sent" <?php echo $ticket['status'] == 'budget_sent' ? 'selected' : ''; ?>>
                                Presupuesto Enviado</option>
                            <option value="budget_approved" <?php echo $ticket['status'] == 'budget_approved' ? 'selected' : ''; ?>>P. Aprobado</option>
                            <option value="active" <?php echo $ticket['status'] == 'active' ? 'selected' : ''; ?>>Activar
                                Servicio</option>
                            <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Cerrar
                                Ticket</option>
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
                    <h4 class="text-white h6 mb-0 fw-bold"><?= __('tickets.direct_communication') ?></h4>
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
                        <?php
                        // System auto-message (user_id = 0 or message_type = 'system')
                        if ($msg['message_type'] === 'system' || empty($msg['user_id'])):
                            ?>
                            <div class="align-self-center text-center w-100 my-2">
                                <div class="d-inline-block px-4 py-3 rounded-4"
                                    style="background: linear-gradient(135deg, rgba(212,175,55,0.12), rgba(48,197,255,0.08)); border: 1px solid rgba(212,175,55,0.25); max-width: 90%; text-align: left;">
                                    <p class="x-small mb-0 text-white" style="white-space: pre-line; line-height: 1.7;">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <span class="material-symbols-outlined text-primary"
                                            style="font-size: 12px;">smart_toy</span>
                                        <span class="x-small text-white-50"><?php echo \Core\Config::get('business.company_name'); ?> Bot •
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
                <form action="<?php echo url('chat/send'); ?>" method="POST" class="d-flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                    <input type="text" name="message" class="form-control bg-steel border-white-10 text-white p-3"
                        placeholder="<?= __('tickets.write_message') ?>" required autocomplete="off">
                    <button type="submit" class="btn btn-primary px-4 d-flex align-items-center justify-content-center">
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
</script>