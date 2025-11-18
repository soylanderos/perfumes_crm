<?php
// variables que vienen del controller

/** @var array $summary */
/** @var array $top_debtors */
/** @var array $recent_activity */
$total_clients        = (int)($summary['total_clients'] ?? 0);
$clients_with_debt    = (int)($summary['clients_with_debt'] ?? 0);
$clients_clear        = (int)($summary['clients_clear'] ?? 0);
$total_receivable     = (float)($summary['total_receivable'] ?? 0);
$new_clients_month    = (int)($summary['new_clients_this_month'] ?? 0);
$sales_this_month     = (float)($summary['sales_this_month'] ?? 0);
$payments_this_month  = (float)($summary['payments_this_month'] ?? 0);
$net_change           = (float)($summary['net_change'] ?? 0);
?>
<div class="container-fluid dashboard-page ">

	<!-- Header -->
	<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
		<div>
			<h1 class="h3 mb-1 fw-semibold">Resumen general</h1>
			<p class="text-muted mb-0">
				Ve cómo van tus clientes, compras y cobros en este periodo.
			</p>
		</div>
		<div class="ms-auto d-flex flex-wrap align-items-center gap-2">
		
		</div>
	</div>

	<!-- Hero KPIs -->
	<div class="row g-3 mb-3">
		<div class="col-12 col-lg-5">
			<div class="dash-hero-card">
				<div class="d-flex justify-content-between align-items-start mb-2">
					<div>
						<div class="dash-hero-label">Saldo total por cobrar</div>
						<div class="dash-hero-amount">
							$<?= number_format($total_receivable, 2) ?>
						</div>
					</div>
					<span class="dash-hero-pill">
						<?= $clients_with_debt ?> clientes con deuda
					</span>
				</div>
				<div class="d-flex flex-wrap gap-3 small text-white-70">
					<span>Total clientes: <strong><?= $total_clients ?></strong></span>
					<span>Al día: <strong><?= $clients_clear ?></strong></span>
					<span>Nuevos este mes: <strong><?= $new_clients_month ?></strong></span>
				</div>
			</div>
		</div>

		<div class="col-6 col-lg-3">
			<div class="dash-kpi-card">
				<div class="dash-kpi-label">Compras este mes</div>
				<div class="dash-kpi-value">
					$<?= number_format($sales_this_month, 2) ?>
				</div>
				<div class="dash-kpi-sub">Monto total registrado</div>
			</div>
		</div>

		<div class="col-6 col-lg-3">
			<div class="dash-kpi-card">
				<div class="dash-kpi-label">Pagos este mes</div>
				<div class="dash-kpi-value text-success">
					$<?= number_format($payments_this_month, 2) ?>
				</div>
				<div class="dash-kpi-sub">
					Neto cartera:
					<span class="<?= $net_change >= 0 ? 'text-danger' : 'text-success' ?>">
						<?= $net_change >= 0 ? '+' : '' ?>$<?= number_format($net_change, 2) ?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Gráfica + Top deudores -->
	<div class="row g-3 mb-3">
		<div class="col-12 col-xl-7">
			<div class="dash-card h-100">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<h2 class="h6 mb-0">Compras vs pagos</h2>
					<span class="badge rounded-pill bg-light text-muted">Últimos 6 meses</span>
				</div>
				<div class="dash-chart-wrapper">
					<canvas id="dashboard_kpi_chart"></canvas>
				</div>
			</div>
		</div>

		<div class="col-12 col-xl-5">
			<div class="dash-card h-100">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<h2 class="h6 mb-0">Top clientes con deuda</h2>
				</div>
				<?php if (!empty($top_debtors)): ?>
					<ul class="list-unstyled mb-0 dash-list">
						<?php foreach ($top_debtors as $c): ?>
							<li class="dash-list-item">
								<div>
									<div class="fw-semibold small">
										<?= htmlspecialchars($c['name']) ?>
									</div>
									<div class="small text-muted">
										Cliente #<?= (int)$c['id'] ?>
									</div>
								</div>
								<div class="fw-semibold small text-danger">
									$<?= number_format($c['balance'], 2) ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<p class="small text-muted mb-0">
						Ningún cliente tiene saldo pendiente. Nada mal 🔥
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Actividad reciente -->
	<div class="row g-3">
		<div class="col-12">
			<div class="dash-card">
				<div class="d-flex justify-content-between align-items-center mb-2">
					<h2 class="h6 mb-0">Actividad reciente</h2>
				</div>
				<?php if (!empty($recent_activity)): ?>
					<ul class="list-unstyled mb-0 dash-list">
						<?php foreach ($recent_activity as $a):
							$is_payment = $a['type'] === 'payment';
							$sign  = $is_payment ? '+' : '-';
							$color = $is_payment ? 'text-success' : 'text-danger';
						?>
							<li class="dash-list-item">
								<div>
									<div class="fw-semibold small">
										<?= htmlspecialchars($a['customer_name']) ?>
									</div>
									<div class="small text-muted">
										<?= date('d/m/Y H:i', strtotime($a['movement_date'])) ?>
										&nbsp;&bull;&nbsp;
										<?= $is_payment ? 'Pago' : 'Cargo' ?>
										<?php if (!empty($a['notes'])): ?>
											&nbsp;&bull;&nbsp;<?= htmlspecialchars($a['notes']) ?>
										<?php endif; ?>
									</div>
								</div>
								<div class="fw-semibold small <?= $color ?>">
									<?= $sign ?>$<?= number_format($a['amount'], 2) ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<p class="small text-muted mb-0">
						Aún no hay movimientos recientes.
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>