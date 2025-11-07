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
                <input id="client_search" type="search" class="form-control" placeholder="Buscar por nombre, teléfono o email…">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select id="client_status" class="form-select">
                <option value="">Todos los estados</option>
                <option value="al_dia">Al día</option>
                <option value="pendiente">Pendiente</option>
                <option value="vencido">Vencido</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select id="client_sort" class="form-select">
                <option value="nombre">Ordenar por: Nombre</option>
                <option value="saldo_desc">Saldo (desc)</option>
                <option value="prox_venc">Próximo vencimiento</option>
            </select>
        </div>
    </div>


    <!-- Lista con cards -->
    <div class="vstack gap-3">
        <div class="container-responsive-350">
            <!-- Card 1 -->
            <?php
            $loop_index = 0;
            foreach ($clients as $c):
                $loop_index++;
                include '../components/card/client_card.php';
            endforeach;

            ?>
        </div>
    </div>
</div>