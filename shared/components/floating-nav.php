<!-- Bottom Floating Navigation -->
<nav class="floating-bottom-nav">
    <ul class="floating-nav-list">
        <!-- Dashboard -->
        <li class="floating-nav-item">
            <button class="floating-nav-btn active"
                id="fetch_dashboard"
                data-controller="dashboard">
                <span class="floating-icon-wrap">
                    <i class="bi bi-grid-1x2-fill"></i>
                </span>
                <span class="floating-label">Dashboard</span>
            </button>
        </li>

        <!-- Clientes -->
        <li class="floating-nav-item">
            <button class="floating-nav-btn"
                id="fetch_clients"
                data-controller="clients">
                <span class="floating-icon-wrap">
                    <i class="bi bi-people-fill"></i>
                </span>
                <span class="floating-label">Clientes</span>
            </button>
        </li>

        <!-- SKUs / Inventario -->
        <li class="floating-nav-item">
            <button class="floating-nav-btn"
                id="fetch_orders"
                data-controller="orders">
                <span class="floating-icon-wrap">
                    <i class="bi bi-box-seam"></i>
                </span>
                <span class="floating-label">Pedidos</span>
            </button>
        </li>

        <!-- Logout (lado derecho) -->
        <li class="floating-nav-item floating-nav-item-right">
            <button class="floating-nav-btn floating-nav-btn-logout"
                id="btn_log_out">
                <span class="floating-icon-wrap">
                    <i class="bi bi-box-arrow-right"></i>
                </span>
            </button>
        </li>
    </ul>
</nav>