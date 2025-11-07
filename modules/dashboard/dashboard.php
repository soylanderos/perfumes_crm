<?php
$payload = [
	'labels' => $labels,
	'sales' => array_map('floatval', $salesData),
	'paid' => array_map('floatval', $paidData),
	'by_status' => $by_status,
	'aging' => $aging,
	'top_customers' => $top_customers,
	'top_debtors' => $top_debtors,
	'top_products' => $top_products
];
$paidPct = ($kpi['total_sales'] > 0) ? ($kpi['total_paid'] / $kpi['total_sales'] * 100) : 0;


?>
<!-- Payload JSON para charts -->
<script type="application/json" id="dashboard-data">
	<?php echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
</script>

<style>
	/* Modo compacto */
	#dashboard_root .card-body {
		padding: .75rem
	}

	#dashboard_root h6 {
		margin-bottom: .5rem
	}

	#dashboard_root .kpi .fs-4 {
		font-size: 1.25rem !important
	}

	#dashboard_root .kpi .small {
		font-size: .8rem !important
	}

	/* alturas pequeñas para charts (Chart.js usa maintainAspectRatio=false) */
	.chart-xs {
		height: 120px
	}

	.chart-sm {
		height: 150px
	}

	.chart-md {
		height: 180px
	}
</style>

<div id="dashboard_root" class="container py-3">

	<!-- KPIs (más compactos) -->
	<div class="row g-2 mb-2">
		<div class="col-6 col-lg-2">
			<div class="card shadow-sm border-0 kpi">
				<div class="card-body">
					<div class="text-secondary small">Ventas</div>
					<div class="fs-4 fw-semibold">$<?= number_format($kpi['total_sales'], 2) ?></div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-2">
			<div class="card shadow-sm border-0 kpi">
				<div class="card-body">
					<div class="text-secondary small">Cobrado</div>
					<div class="fs-4 fw-semibold">$<?= number_format($kpi['total_paid'], 2) ?></div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-2">
			<div class="card shadow-sm border-0 kpi">
				<div class="card-body">
					<div class="text-secondary small">Por cobrar</div>
					<div class="fs-4 fw-semibold">$<?= number_format($kpi['total_due'], 2) ?></div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-2">
			<div class="card shadow-sm border-0 kpi">
				<div class="card-body">
					<div class="text-secondary small">% Pagado</div>
					<div class="fs-4 fw-semibold"><?= number_format($paidPct, 1) ?>%</div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-2">
			<div class="card shadow-sm border-0 kpi">
				<div class="card-body">
					<div class="text-secondary small">Órdenes</div>
					<div class="fs-4 fw-semibold"><?= number_format($kpi['orders_count']) ?></div>
				</div>
			</div>
		</div>
		<div class="col-6 col-lg-2">
			<div class="card shadow-sm border-0 kpi">
				<div class="card-body">
					<div class="text-secondary small">Ticket promedio</div>
					<div class="fs-4 fw-semibold">$<?= number_format($kpi['avg_ticket'], 2) ?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Fila 1: 4 columnas (tops) -->
	<div class="row g-2 mb-2">
		<div class="col-12 col-lg-3">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Top clientes (ventas)</h6>
					<canvas id="chart_top_customers" class="chart-xs"></canvas>
				</div>
			</div>
		</div>
		<div class="col-12 col-lg-3">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Top deudores</h6>
					<canvas id="chart_top_debtors" class="chart-xs"></canvas>
				</div>
			</div>
		</div>
		<div class="col-12 col-lg-3">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Top productos (importe)</h6>
					<canvas id="chart_top_products_total" class="chart-xs"></canvas>
				</div>
			</div>
		</div>
		<div class="col-12 col-lg-3">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Top productos (cantidad)</h6>
					<canvas id="chart_top_products_qty" class="chart-xs"></canvas>
				</div>
			</div>
		</div>
	</div>

	<!-- Fila 2: alertas compactas -->
	<div class="row g-2 mb-2">
		<div class="col-12 col-lg-6">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6 class="mb-2">Stock bajo</h6>
					<?php if ($low_stock): ?>
						<div class="table-responsive">
							<table class="table table-sm align-middle mb-0">
								<thead>
									<tr>
										<th>SKU</th>
										<th>Producto</th>
										<th class="text-end">Stock</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($low_stock as $it): ?>
										<tr>
											<td><?= htmlspecialchars($it['sku']) ?></td>
											<td><?= htmlspecialchars($it['name']) ?></td>
											<td class="text-end">
												<span class="badge text-bg-<?= (int)$it['stock'] <= 0 ? 'danger' : ((int)$it['stock'] <= 3 ? 'warning' : 'secondary') ?>">
													<?= (int)$it['stock'] ?>
												</span>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php else: ?>
						<div class="text-secondary small">Sin alertas de stock.</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-6">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6 class="mb-2">Vencimientos</h6>
					<div class="row">
						<div class="col-12 col-md-6">
							<div class="small text-secondary mb-1">Próximos 7 días</div>
							<ul class="list-group list-group-flush small">
								<?php foreach ($due_soon as $o): ?>
									<li class="list-group-item px-0 d-flex justify-content-between">
										<span>#<?= (int)$o['id'] ?> · $<?= number_format($o['balance'], 2) ?></span>
										<span class="text-secondary"><?= date('d/m', strtotime($o['due_date'])) ?></span>
									</li>
								<?php endforeach;
								if (!$due_soon): ?>
									<li class="list-group-item px-0 text-secondary">Nada por ahora</li>
								<?php endif; ?>
							</ul>
						</div>
						<div class="col-12 col-md-6">
							<div class="small text-secondary mb-1">Vencidos</div>
							<ul class="list-group list-group-flush small">
								<?php foreach ($overdue as $o): ?>
									<li class="list-group-item px-0 d-flex justify-content-between">
										<span>#<?= (int)$o['id'] ?> · $<?= number_format($o['balance'], 2) ?></span>
										<span class="badge text-bg-danger"><?= date('d/m', strtotime($o['due_date'])) ?></span>
									</li>
								<?php endforeach;
								if (!$overdue): ?>
									<li class="list-group-item px-0 text-secondary">Sin vencidos 🎉</li>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<!-- Fila 3: 3 columnas -->
	<div class="row g-2 mb-2">
		<div class="col-12 col-lg-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Ventas vs Cobros</h6>
					<canvas id="chart_sales_paid" class="chart-sm"></canvas>
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Estatus de órdenes</h6>
					<canvas id="chart_status" class="chart-xs"></canvas>
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-4">
			<div class="card shadow-sm border-0 h-100">
				<div class="card-body">
					<h6>Aging cartera</h6>
					<canvas id="chart_aging" class="chart-xs"></canvas>
				</div>
			</div>
		</div>
	</div>
</div>