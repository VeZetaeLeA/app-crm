<div class="row g-4">
    <div class="col-12 d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="text-white fw-black mb-1">Directorio de Usuarios 👥</h2>
            <p class="text-white-50">Gestiona el acceso, roles y permisos de la plataforma.</p>
        </div>
        <button class="btn btn-primary btn-sm px-4 fw-bold rounded-pill shadow-gold">Invitar Usuario</button>
    </div>

    <!-- User Table -->
    <div class="col-lg-12">
        <div class="glass-morphism rounded-5 border-white-10 overflow-hidden shadow-2xl">
            <div
                class="p-4 border-bottom border-white-10 bg-white-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <h5 class="text-white h6 mb-0 fw-bold uppercase tracking-widest d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary">groups</span>
                    Miembros Activos
                </h5>
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                    <!-- Filter Buttons -->
                    <div class="btn-group role-filters" role="group">
                        <button type="button" class="btn btn-sm btn-primary fw-bold px-3 active"
                            data-filter="all">Todos</button>
                        <button type="button" class="btn btn-sm btn-outline-white border-white-10 fw-bold px-3"
                            data-filter="admin"><span
                                class="material-symbols-outlined fs-6 align-middle me-1">shield_person</span>
                            Admins</button>
                        <button type="button" class="btn btn-sm btn-outline-white border-white-10 fw-bold px-3"
                            data-filter="staff"><span
                                class="material-symbols-outlined fs-6 align-middle me-1">support_agent</span>
                            Staff</button>
                        <button type="button" class="btn btn-sm btn-outline-white border-white-10 fw-bold px-3"
                            data-filter="client"><span
                                class="material-symbols-outlined fs-6 align-middle me-1">group</span> Clientes</button>
                    </div>

                    <div class="input-group input-group-sm w-auto">
                        <span class="input-group-text bg-steel border-white-10 text-white-50"><span
                                class="material-symbols-outlined fs-6">search</span></span>
                        <input type="text" id="userSearchInput" class="form-control bg-steel border-white-10 text-white"
                            placeholder="Filtrar por nombre o email...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 align-middle">
                    <thead class="bg-deep-black">
                        <tr class="x-small uppercase text-white-50 tracking-widest">
                            <th class="p-4 border-0">Usuario</th>
                            <th class="p-4 border-0">Email / Contacto</th>
                            <th class="p-4 border-0">Rol Asignado</th>
                            <th class="p-4 border-0">Empresa</th>
                            <th class="p-4 border-0 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr class="user-row" data-role="<?php echo strtolower($u['role']); ?>"
                                data-search="<?php echo strtolower($u['name'] . ' ' . $u['email'] . ' ' . ($u['company'] ?? '')); ?>">
                                <td class="p-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-steel d-flex align-items-center justify-content-center text-primary shadow-gold font-monospace fw-bold"
                                            style="width: 42px; height: 42px; border: 1px solid var(--elegant-gold);">
                                            <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white d-flex align-items-center gap-2">
                                                <?php echo $u['name']; ?>
                                                <?php if (!$u['is_active']): ?>
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger x-small border border-danger border-opacity-25">INACTIVO</span>
                                                <?php endif; ?>
                                                <?php if ($u['two_factor_enabled']): ?>
                                                    <span class="material-symbols-outlined text-success fs-6"
                                                        title="2FA Activado">verified_user</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="x-small text-white-50 mt-1">ID:
                                                <?php echo $u['id']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-white-50 small">
                                        <?php echo $u['email']; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <form action="<?php echo url('admin/users/updateRole'); ?>" method="POST"
                                        class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="role"
                                            class="form-select form-select-sm bg-steel border-white-10 text-white x-small uppercase fw-bold"
                                            onchange="this.form.submit()" style="width: auto;">
                                            <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin
                                            </option>
                                            <option value="staff" <?php echo $u['role'] == 'staff' ? 'selected' : ''; ?>>Staff
                                            </option>
                                            <option value="client" <?php echo $u['role'] == 'client' ? 'selected' : ''; ?>>
                                                Cliente</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="p-4 text-white-50 small">
                                    <?php echo $u['company'] ?? 'N/A'; ?>
                                </td>
                                <td class="p-4 text-end">
                                    <div class="btn-group gap-2">
                                        <?php if ($u['id'] != \Core\Auth::user()['id']): ?>
                                            <?php if ($u['is_active']): ?>
                                                <a href="<?php echo url('admin/users/edit/' . $u['id']); ?>"
                                                    class="btn btn-outline-white btn-sm rounded-3 border-white-10" title="Editar">
                                                    <span class="material-symbols-outlined fs-6 align-middle">edit</span>
                                                </a>
                                                <a href="<?php echo url('admin/users/delete/' . $u['id']); ?>"
                                                    class="btn btn-outline-warning btn-sm rounded-3" title="Revocar Acceso"
                                                    onclick="return confirm('¿Seguro que deseas revocar el acceso a este usuario?')">
                                                    <span class="material-symbols-outlined fs-6 align-middle">block</span>
                                                </a>
                                            <?php else: ?>
                                                <form action="<?php echo url('admin/users/update'); ?>" method="POST"
                                                    class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                    <input type="hidden" name="name" value="<?php echo $u['name']; ?>">
                                                    <input type="hidden" name="email" value="<?php echo $u['email']; ?>">
                                                    <input type="hidden" name="role" value="<?php echo $u['role']; ?>">
                                                    <input type="hidden" name="is_active" value="1">
                                                    <button type="submit" class="btn btn-outline-success btn-sm rounded-3"
                                                        title="Reactivar">
                                                        <span class="material-symbols-outlined fs-6 align-middle">undo</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <a href="<?php echo url('admin/users/destroy/' . $u['id']); ?>"
                                                class="btn btn-outline-danger btn-sm rounded-3" title="Eliminar Permanentemente"
                                                onclick="return confirm('¡CUIDADO! Esto borrará al usuario y TODO su historial de forma IRREVERSIBLE. ¿Estás seguro?')">
                                                <span class="material-symbols-outlined fs-6 align-middle">delete_forever</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-white-10 text-white-50 small">Tú</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-white-5 border-top border-white-10 text-center">
                <span class="text-white-50 x-small uppercase tracking-widest">Total de registros:
                    <span id="visibleUserCount"><?php echo count($users); ?></span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('userSearchInput');
        const filterButtons = document.querySelectorAll('.role-filters button');
        const userRows = document.querySelectorAll('.user-row');
        const countDisplay = document.getElementById('visibleUserCount');
        let currentRoleFilter = 'all';

        function filterUsers() {
            const query = searchInput.value.toLowerCase();
            let visibleCount = 0;

            userRows.forEach(row => {
                const role = row.dataset.role;
                // Treat super_admin as admin for filtering
                const mappedRole = role === 'super_admin' ? 'admin' : role;
                const searchContent = row.dataset.search;

                const matchesRole = currentRoleFilter === 'all' || mappedRole === currentRoleFilter;
                const matchesSearch = query === '' || searchContent.includes(query);

                if (matchesRole && matchesSearch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            countDisplay.textContent = visibleCount;
        }

        // Role Filter Buttons
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                // Update active state
                filterButtons.forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-white');
                });
                this.classList.remove('btn-outline-white');
                this.classList.add('btn-primary', 'active');

                // Apply filter
                currentRoleFilter = this.dataset.filter;
                filterUsers();
            });
        });

        // Search Input
        if (searchInput) {
            searchInput.addEventListener('input', filterUsers);
        }
    });
</script>