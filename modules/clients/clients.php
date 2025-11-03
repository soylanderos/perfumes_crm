<div class="container py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 mb-0">Clientes</h1>
            <small class="text-secondary">Gestiona tus clientes y sus saldos</small>
        </div>
        <div class="d-none d-sm-block">
            <button id="btn_add_new_client" class="btn btn-primary">+ Nuevo cliente</button>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" class="form-control" placeholder="Buscar por nombre, teléfono o email…">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select">
                <option value="">Todos los estados</option>
                <option value="al_dia">Al día</option>
                <option value="pendiente">Pendiente</option>
                <option value="vencido">Vencido</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select">
                <option value="">Ordenar por</option>
                <option value="nombre">Nombre</option>
                <option value="saldo_desc">Saldo (desc)</option>
                <option value="prox_venc">Próximo vencimiento</option>
            </select>
        </div>

        <!-- Botón móvil -->
        <div class="col-12 d-sm-none">
            <a href="#" class="btn btn-primary w-100">+ Nuevo cliente</a>
        </div>
    </div>

    <!-- Lista con cards -->
    <div class="vstack gap-3">

        <!-- Card 1 -->
        <?php
        foreach ($clients as $c):
            include '../components/card/client_card.php';
        endforeach;

        ?>
    </div>
</div>