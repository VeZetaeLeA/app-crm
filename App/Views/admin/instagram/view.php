<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="h3 text-white mb-1 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined text-accent">calendar_month</span>
            <?php echo htmlspecialchars($calendar['week_label']); ?>
        </h2>
        <p class="text-white-50 small mb-0">Inicia el <?php echo date('d/m/Y', strtotime($calendar['start_date'])); ?></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="<?php echo url('admin/instagram'); ?>" class="btn btn-outline-secondary text-white border-white-10 hover-white">Volver</a>
        <?php if ($calendar['status'] !== 'finalized'): ?>
            <form action="<?php echo url('admin/instagram/finalize'); ?>" method="POST" class="m-0">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo $calendar['id']; ?>">
                <button type="submit" class="btn btn-success shadow-neon text-white fw-bold d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span> Finalizar y Exportar
                </button>
            </form>
        <?php else: ?>
            <a href="<?php echo url('admin/instagram/downloadCsv?id=' . $calendar['id']); ?>" class="btn btn-primary shadow-gold text-midnight fw-bold d-flex align-items-center gap-2">
                <span class="material-symbols-outlined">download</span> Descargar CSV
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($calendar['posts'] as $post): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card glass-morphism border-white-10 h-100 d-flex flex-column hover-card-lift">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 uppercase tracking-widest mb-2">
                                <?php echo $post['day_of_week']; ?>
                            </span>
                            <h3 class="h5 text-white mb-1"><?php echo htmlspecialchars($post['internal_title']); ?></h3>
                        </div>
                        <span class="badge bg-white-10 text-white-50 uppercase tracking-widest">
                            <?php echo htmlspecialchars($post['post_format']); ?>
                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 text-white-50 x-small uppercase tracking-widest mb-3">
                        <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">calendar_today</span> <?php echo date('d/m/Y', strtotime($post['publish_date'])); ?></span>
                        <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined fs-6">schedule</span> <?php echo date('H:i', strtotime($post['publish_time'])); ?></span>
                        <span class="d-flex align-items-center gap-1 text-accent"><span class="material-symbols-outlined fs-6">target</span> <?php echo htmlspecialchars($post['strategic_pilar']); ?></span>
                    </div>

                    <div class="bg-black bg-opacity-50 border border-white-5 rounded-3 p-3 mb-3 flex-grow-1">
                        <p class="text-white-75 small mb-0 lh-base" style="white-space: pre-wrap;"><?php echo htmlspecialchars($post['copy_text']); ?></p>
                        <?php if ($post['cta_text']): ?>
                            <div class="mt-3 pt-3 border-top border-white-5">
                                <p class="text-accent small fw-bold mb-0">CTA: <?php echo htmlspecialchars($post['cta_text']); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="mt-2 text-primary x-small">
                            <?php echo htmlspecialchars($post['hashtags']); ?>
                        </div>
                    </div>

                    <div class="bg-white-5 border border-white-10 rounded-3 p-3 mb-4">
                        <span class="d-block text-white-50 x-small uppercase tracking-widest mb-1 d-flex align-items-center gap-1">
                            <span class="material-symbols-outlined fs-6">image_search</span> Visual Prompt
                        </span>
                        <p class="text-white-50 small mb-0 fst-italic">
                            <?php echo htmlspecialchars($post['visual_prompt']); ?>
                        </p>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <button onclick="editPost(<?php echo htmlspecialchars(json_encode($post)); ?>)" class="btn btn-sm btn-outline-light w-100 border-white-10 hover-white d-flex justify-content-center align-items-center gap-1">
                            <span class="material-symbols-outlined fs-6">edit</span> Editar
                        </button>
                        <?php if ($calendar['status'] !== 'finalized'): ?>
                            <form action="<?php echo url('admin/instagram/regeneratePost'); ?>" method="POST" class="w-100 m-0" onsubmit="return confirm('¿Regenerar este contenido con IA? Se perderán los cambios manuales.')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="calendar_id" value="<?php echo $calendar['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info w-100 d-flex justify-content-center align-items-center gap-1">
                                    <span class="material-symbols-outlined fs-6">cycle</span> Re-generar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-midnight border border-white-10 glass-morphism">
            <div class="modal-header border-bottom border-white-10">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" id="editModalLabel">
                    <span class="material-symbols-outlined text-primary">edit_square</span> 
                    <span id="modalTitleText">Editar Post</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo url('admin/instagram/updatePost'); ?>" method="POST">
                <div class="modal-body p-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="post_id" id="edit_post_id">
                    <input type="hidden" name="calendar_id" value="<?php echo $calendar['id']; ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase tracking-widest">Título Interno</label>
                            <input type="text" name="internal_title" id="edit_title" class="form-control bg-black border-white-10 text-white focus-ring-primary" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50 x-small uppercase tracking-widest">Fecha Pub.</label>
                            <input type="date" name="publish_date" id="edit_date" class="form-control bg-black border-white-10 text-white focus-ring-primary" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50 x-small uppercase tracking-widest">Hora Pub.</label>
                            <input type="time" name="publish_time" id="edit_time" class="form-control bg-black border-white-10 text-white focus-ring-primary" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white-50 x-small uppercase tracking-widest">Copy del Post (Instagram Caption)</label>
                        <textarea name="copy_text" id="edit_copy" rows="6" class="form-control bg-black border-white-10 text-white focus-ring-primary" required></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase tracking-widest">CTA (Llamado a la acción)</label>
                            <input type="text" name="cta_text" id="edit_cta" class="form-control bg-black border-white-10 text-white focus-ring-primary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase tracking-widest">Hashtags</label>
                            <input type="text" name="hashtags" id="edit_hashtags" class="form-control bg-black border-white-10 text-white focus-ring-primary">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-white-50 x-small uppercase tracking-widest">Visual Prompt (Image Gen)</label>
                        <textarea name="visual_prompt" id="edit_prompt" rows="2" class="form-control bg-black border-white-10 text-white focus-ring-primary"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top border-white-10 bg-black bg-opacity-25">
                    <button type="button" class="btn btn-link text-white-50 text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary shadow-gold text-midnight fw-bold px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hover-card-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.hover-card-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 40px rgba(0,255,255,0.05) !important; border-color: rgba(255,255,255,0.2) !important; }
</style>

<script>
function editPost(post) {
    document.getElementById('edit_post_id').value = post.id;
    document.getElementById('edit_title').value = post.internal_title;
    document.getElementById('edit_date').value = post.publish_date;
    document.getElementById('edit_time').value = post.publish_time;
    document.getElementById('edit_copy').value = post.copy_text;
    document.getElementById('edit_cta').value = post.cta_text;
    document.getElementById('edit_hashtags').value = post.hashtags;
    document.getElementById('edit_prompt').value = post.visual_prompt;
    
    document.getElementById('modalTitleText').innerText = 'Editar Post: ' + post.day_of_week;
    
    // Si no está inicializado, inicializar el modal de bootstrap
    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}
</script>
