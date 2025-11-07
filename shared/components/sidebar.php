<!-- Top Navbar para móviles -->
<header class="mobile-navbar">
    <button class="menu-toggle-btn">
        <span class="material-symbols-rounded">menu</span>
    </button>
    <h1 class="mobile-navbar-title">Menu</h1>
</header>

<!-- Sidebar existente -->
<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav-list primary-nav">
            <li class="nav-item sidebar-item" data-controller="dashboard" id="fetch_dashboard">
                <a href="#" class="nav-link">
                    <span class="material-symbols-rounded">dashboard</span>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            <li class="nav-item sidebar-item" data-controller="clients" id="fetch_clients">
                <a href="#" class="nav-link">
                    <span class="material-symbols-rounded">group</span>
                    <span class="nav-label">Clientes</span>
                </a>
            </li>

            <li class="nav-item sidebar-item" data-controller="skus" id="fetch_skus">
                <a href="#" class="nav-link">
                    <span class="material-symbols-rounded">hand_package</span>
                    <span class="nav-label">Products</span>
                </a>
            </li>

            <li class="nav-item sidebar-item" data-controller="products" id="fetch_products">
                <a href="#" class="nav-link">
                    <span class="material-symbols-rounded">two_pager_store</span>
                    <span class="nav-label">Catalogo</span>
                </a>
            </li>
        </ul>

        <ul class="nav-list secondary-nav">
            <li class="nav-item" id="btn_log_out">
                <a href="#" class="nav-link">
                    <span class="material-symbols-rounded">logout</span>
                    <span class="nav-label">Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>