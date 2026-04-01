<div class="row g-4 mb-5" id="userImport">
    <div class="col-12 mb-2">
        <h2 class="text-white fw-black mb-1">Importar Clientes Masivamente 👥</h2>
        <p class="text-white-50">Sube un archivo CSV con tus contactos de negocio para cargarlos instantáneamente.</p>
    </div>

    <div class="col-md-8">
        <div class="glass-morphism border-white-10 p-5 rounded-4 shadow-lg">
            <form action="<?php echo url('admin/users/import'); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <div class="mb-5 text-center p-5 border-dashed border-white-20 rounded-4 bg-white-02 hover-border-gold transition-all" 
                     id="drop-zone" onclick="document.getElementById('csv_file').click()">
                    <span class="material-symbols-outlined display-4 text-white-30 mb-3">upload_file</span>
                    <h5 class="text-white fw-bold">Arrastra tu archivo CSV aquí</h5>
                    <p class="text-white-50 x-small">O haz clic para seleccionar desde tu computadora</p>
                    <input type="file" name="csv_file" id="csv_file" class="d-none" accept=".csv" required>
                    <div id="file-name" class="mt-2 text-elegant-gold fw-bold small"></div>
                </div>

                <div class="mb-4">
                    <h6 class="text-white-70 small fw-black mb-3 border-bottom border-white-10 pb-2">INSTRUCCIONES DE FORMATO</h6>
                    <ul class="text-white-50 small ps-3">
                        <li>El archivo debe ser un **CSV** separado por comas.</li>
                        <li>La primera fila se considerará el encabezado y será ignorada.</li>
                        <li>Las columnas deben tener este orden: 
                            <code class="text-elegant-gold fw-bold">Nombre, Email, Empresa, Teléfono, Rol</code>
                        </li>
                        <li>El **Nombre** y **Email** son obligatorios.</li>
                        <li>Si el email ya existe, ese registro será ignorado en el reporte final.</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-5">
                    <a href="<?php echo url('assets/samples/user_import_template.csv'); ?>" class="btn btn-link text-white-50 text-decoration-none small d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined fs-6">download</span>
                        Descargar Plantilla CSV
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-gold">
                        Procesar Importación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="glass-morphism border-white-10 p-4 rounded-4 h-100 bg-black-20">
            <h6 class="text-white fw-black mb-3">CONSEJO ENTERPRISE 💡</h6>
            <p class="text-white-50 small mb-4">
                "La calidad de los datos es vital. Antes de importar, asegúrate de que los emails no tengan espacios en blanco y los teléfonos incluyan el código de país."
            </p>
            <div class="p-3 rounded-3 bg-white-05 border-white-10">
                <span class="d-block text-white-70 x-small fw-bold mb-1">CAPACIDAD MÁXIMA</span>
                <span class="text-white fw-bold">1,000 registros por archivo</span>
            </div>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('csv_file');
    const fileNameDisplay = document.getElementById('file-name');
    const dropZone = document.getElementById('drop-zone');

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            fileNameDisplay.textContent = 'Seleccionado: ' + e.target.files[0].name;
            dropZone.classList.add('border-gold');
        }
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('bg-white-10');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-white-10');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-white-10');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            fileNameDisplay.textContent = 'Seleccionado: ' + e.dataTransfer.files[0].name;
        }
    });
</script>

<style>
    .border-dashed { border-style: dashed !important; }
    .bg-white-02 { background: rgba(255,255,255,0.02); }
    .bg-white-05 { background: rgba(255,255,255,0.05); }
    .bg-white-10 { background: rgba(255,255,255,0.1); }
    .bg-black-20 { background: rgba(0,0,0,0.2); }
    .border-white-20 { border-color: rgba(255,255,255,0.2) !important; }
    .hover-border-gold:hover { border-color: var(--vzl-color-gold) !important; cursor: pointer; }
    #drop-zone.border-gold { border-color: var(--vzl-color-gold) !important; }
</style>
