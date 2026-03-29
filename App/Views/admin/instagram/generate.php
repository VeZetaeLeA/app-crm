<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="mb-4 text-center">
            <span class="material-symbols-outlined display-4 text-accent mb-2">robot_2</span>
            <h2 class="h3 text-white">Generar Estrategia Semanal</h2>
            <p class="text-white-50">Configura la fecha de inicio para que la IA orqueste tus 7 posts semanales.</p>
        </div>

        <div class="card glass-morphism border-white-10 p-4 p-md-5">
            <form action="<?php echo url('admin/instagram/generate'); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="mb-4">
                    <label class="form-label text-white-50 small uppercase tracking-widest">Fecha de Inicio (Lunes preferentemente):</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-d', strtotime('next monday')); ?>" required class="form-control bg-black border-white-10 text-white focus-ring-accent">
                </div>

                <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-4 p-4 mb-4">
                    <h4 class="h6 text-primary fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">track_changes</span> Lógica de Generación:
                    </h4>
                    <ul class="text-white-50 small mb-0 lh-lg">
                        <li>7 posts optimizados para el algoritmo de Instagram.</li>
                        <li>Balance entre Pilares: Datos, Transformación y Resultados.</li>
                        <li>Sugerencia de horarios B2B.</li>
                        <li>Visual Prompts para generación de imágenes (Midjourney / DALL-E).</li>
                        <li>Tono de voz alineado con la identidad corporativa de <?= \Core\Config::get('business.company_name') ?>.</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5">
                    <a href="<?php echo url('admin/instagram'); ?>" class="btn btn-link text-white-50 text-decoration-none hover-white">Cancelar</a>
                    <button type="submit" class="btn btn-primary shadow-gold text-midnight fw-bold px-4 py-2 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">auto_awesome</span> Generar con IA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
