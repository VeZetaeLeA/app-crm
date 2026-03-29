<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="text-white fw-black mb-1">Editar Perfil de Usuario 🛠️</h2>
                <p class="text-white-50">Actualiza la información, rol y estado de acceso de este usuario.</p>
            </div>
            <a href="<?php echo url('admin/users'); ?>"
                class="btn btn-outline-white btn-sm px-4 rounded-pill border-white-10 text-white-50">
                Cancelar
            </a>
        </div>

        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden shadow-2xl">
            <div class="p-4">
                <form action="<?php echo url('admin/users/update'); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                    <div class="row g-4">
                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase fw-bold tracking-widest">Nombre
                                Completo</label>
                            <input type="text" name="name"
                                class="form-control bg-steel border-white-10 text-white p-3 rounded-4"
                                value="<?php echo $user['name']; ?>" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase fw-bold tracking-widest">Correo
                                Electrónico</label>
                            <input type="email" name="email"
                                class="form-control bg-steel border-white-10 text-white p-3 rounded-4"
                                value="<?php echo $user['email']; ?>" required>
                        </div>

                        <!-- Company -->
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase fw-bold tracking-widest">Empresa /
                                Organización</label>
                            <input type="text" name="company"
                                class="form-control bg-steel border-white-10 text-white p-3 rounded-4"
                                value="<?php echo $user['company']; ?>">
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase fw-bold tracking-widest">Teléfono
                                de Contacto</label>
                            <input type="text" name="phone"
                                class="form-control bg-steel border-white-10 text-white p-3 rounded-4"
                                value="<?php echo $user['phone']; ?>">
                        </div>

                        <!-- Role -->
                        <div class="col-md-6">
                            <label class="form-label text-white-50 x-small uppercase fw-bold tracking-widest">Rol del
                                Sistema</label>
                            <select name="role" class="form-select bg-steel border-white-10 text-white p-3 rounded-4">
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>
                                    Administrador (Nivel 0)</option>
                                <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff de
                                    Ingeniería</option>
                                <option value="client" <?php echo $user['role'] == 'client' ? 'selected' : ''; ?>>Cliente
                                    Externo</option>
                            </select>
                        </div>

                        <!-- Security / Password Section -->
                        <div class="col-12 mt-4 pt-4 border-top border-white-5">
                            <h5 class="text-white h6 fw-bold mb-3 d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined fs-5 text-primary">security</span>
                                Seguridad y Acceso
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label
                                        class="form-label text-white-50 x-small uppercase fw-bold tracking-widest">Nueva
                                        Contraseña</label>
                                    <input type="password" name="password"
                                        class="form-control bg-steel border-white-10 text-white p-3 rounded-4"
                                        placeholder="Dejar en blanco para no cambiar">
                                    <div class="x-small text-white-50 mt-1 italic">Solo rellena este campo si deseas
                                        resetear la contraseña del usuario.</div>
                                </div>
                                <div class="col-md-6 d-flex align-items-center mt-md-4">
                                    <div
                                        class="form-check form-switch p-3 bg-white-5 rounded-4 border border-white-10 w-100 ps-5">
                                        <input class="form-check-input ms-n4" type="checkbox" name="is_active"
                                            id="isActive" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-white small ms-2" for="isActive">Cuenta
                                            Activa
                                            (Acceso Permitido)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="col-12 mt-5">
                            <button type="submit"
                                class="btn btn-primary w-100 py-3 rounded-pill fw-bold uppercase tracking-widest shadow-gold">
                                Guardar Cambios del Usuario
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-4 bg-white-5 border-top border-white-10 d-flex align-items-center justify-content-between">
                <span class="text-white-50 x-small uppercase tracking-widest">ID de Registro:
                    <?php echo $user['id']; ?>
                </span>
                <span class="text-white-50 x-small uppercase tracking-widest">Creado:
                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                </span>
            </div>
        </div>
    </div>
</div>