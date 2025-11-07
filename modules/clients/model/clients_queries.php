<?php
function fetch_all_clients(PDO $db): array {
	try {
		// Opcional: filtros (búsqueda/estado) — pásalos por parámetro si gustas
		$sql = "SELECT
				c.id                         AS customer_id,
				c.name,
				c.phone,
				c.email,
				COALESCE(s.tot_amount,0)     AS total_amount,
				COALESCE(s.tot_paid,0)       AS paid_amount,
				COALESCE(s.tot_amount,0) - COALESCE(s.tot_paid,0) AS balance,

				lp.payment_date              AS last_payment_date,
				lp.amount                    AS last_payment_amount,

				nd.next_due_date,
				CASE
					WHEN (COALESCE(s.tot_amount,0) - COALESCE(s.tot_paid,0)) <= 0 THEN 'al_dia'
					WHEN ov.has_overdue = 1 THEN 'vencido'
					ELSE 'pendiente'
				END AS status
			FROM customers c
			/* Sumas por cliente (total y pagado) */
			LEFT JOIN (
				SELECT o.customer_id,
					SUM(o.total_amount) AS tot_amount,
					SUM(o.paid_amount)  AS tot_paid
				FROM orders o
				GROUP BY o.customer_id
			) s ON s.customer_id = c.id

			/* Último pago por cliente (fecha + monto) */
			LEFT JOIN (
				SELECT p.customer_id, p.amount, p.payment_date
				FROM payments p
				INNER JOIN (
					SELECT customer_id, MAX(payment_date) AS max_dt
					FROM payments
					GROUP BY customer_id
				) x ON x.customer_id = p.customer_id AND x.max_dt = p.payment_date
			) lp ON lp.customer_id = c.id

			/* Próximo vencimiento (cuota pendiente más cercana; si no hay cuotas, usa due_date de la orden con saldo) */
			LEFT JOIN (
				SELECT o.customer_id,
						MIN(
						CASE
						WHEN i.id IS NOT NULL AND i.paid_amount < i.amount THEN i.due_date
						WHEN i.id IS NULL AND o.paid_amount < o.total_amount THEN o.due_date
						ELSE NULL
						END
					) AS next_due_date
				FROM orders o
				LEFT JOIN installments i ON i.order_id = o.id
				GROUP BY o.customer_id
			) nd ON nd.customer_id = c.id

			/* Flag de morosidad (existe algo vencido sin cubrir) */
			LEFT JOIN (
				SELECT o.customer_id,
					MAX(
						CASE
						WHEN (i.id IS NOT NULL AND i.due_date < CURRENT_DATE AND i.paid_amount < i.amount)
							OR (i.id IS NULL AND o.due_date < CURRENT_DATE AND o.paid_amount < o.total_amount)
						THEN 1 ELSE 0
						END
					) AS has_overdue
				FROM orders o
				LEFT JOIN installments i ON i.order_id = o.id
				GROUP BY o.customer_id
			) ov ON ov.customer_id = c.id

			ORDER BY c.name ASC;
			";

		$stmt = $db->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $rows;
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function create_client($db, $name, $preferred_day, $phone, $email, $address, $notes) {
	try {
		$query = "INSERT INTO customers (name, preferred_day, phone, email, address, notes, created_at)
					VALUES (:name, :preferred_day, :phone, :email, :address, :notes, NOW())";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':preferred_day', $preferred_day, PDO::PARAM_INT);
		$stmt->bindValue(':phone', $phone);
		$stmt->bindValue(':email', $email);
		$stmt->bindValue(':address', $address);
		$stmt->bindValue(':notes', $notes);
		$stmt->execute();
		return $db->lastInsertId();
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function fetch_client_details($db, $customer_id) {
    try {
		$query = "SELECT * FROM customers WHERE id = :customer_id";

		$stmt = $db->prepare($query);
		$stmt->bindValue(':customer_id', $customer_id);
		$stmt->execute();
		$client = $stmt->fetch(PDO::FETCH_ASSOC);
		return $client;
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function update_client($db, $customer_id, $name, $preferred_day, $phone, $email, $address, $notes) {
	try {
		$query = "UPDATE customers
					SET name = :name,
						preferred_day = :preferred_day,
						phone = :phone,
						email = :email,
						address = :address,
						notes = :notes
					WHERE id = :customer_id";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':preferred_day', $preferred_day, PDO::PARAM_INT);
		$stmt->bindValue(':phone', $phone);
		$stmt->bindValue(':email', $email);
		$stmt->bindValue(':address', $address);
		$stmt->bindValue(':notes', $notes);
		$stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
		$stmt->execute();
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function fetch_client_orders(PDO $db, int $customer_id): array {
	try {
		if ($customer_id <= 0) return [];

		// 1) Resumen por orden (incluye último pago y status derivado)
		$sqlOrders = "
			SELECT
				o.id,
				o.customer_id,
				o.total_amount,
				o.paid_amount,
				(o.total_amount - o.paid_amount) AS balance,
				o.status,
				o.created_at,
				o.due_date,

				-- último pago (fecha y monto)
				(SELECT MAX(p.payment_date) FROM payments p WHERE p.order_id = o.id) AS last_payment_date,
				(SELECT p2.amount
				FROM payments p2
				WHERE p2.order_id = o.id
				ORDER BY p2.payment_date DESC, p2.id DESC
				LIMIT 1) AS last_payment_amount,

				-- status derivado para UI
				CASE
				WHEN o.paid_amount >= o.total_amount THEN 'pagada'
				WHEN o.due_date IS NOT NULL AND o.due_date < CURRENT_DATE AND o.paid_amount < o.total_amount THEN 'vencida'
				WHEN o.paid_amount > 0 THEN 'parcial'
				ELSE 'activa'
				END AS derived_status
			FROM orders o
			WHERE o.customer_id = :cid
			ORDER BY o.id DESC
		";

		$st = $db->prepare($sqlOrders);
		$st->bindValue(':cid', $customer_id, PDO::PARAM_INT);
		$st->execute();
		$orders = $st->fetchAll(PDO::FETCH_ASSOC);
		if (!$orders) return [];

		// Mapa order_id -> índice en $orders
		$idxById = [];
		$orderIds = [];
		foreach ($orders as $i => $o) {
			$orders[$i]['items'] = [];
			$idxById[(int)$o['id']] = $i;
			$orderIds[] = (int)$o['id'];
		}

		// 2) Items de todas las órdenes en un solo query
		$in = implode(',', array_fill(0, count($orderIds), '?'));
		$sqlItems = "
			SELECT
				oi.order_id,
				oi.product_id,
				oi.qty,
				oi.unit_price,
				oi.subtotal,
				p.sku,
				p.name AS product_name
			FROM order_items oi
			LEFT JOIN products p ON p.id = oi.product_id
			WHERE oi.order_id IN ($in)
			ORDER BY oi.order_id ASC, oi.id ASC
		";
		$sti = $db->prepare($sqlItems);
		foreach ($orderIds as $k => $oid) {
			$sti->bindValue($k+1, $oid, PDO::PARAM_INT);
		}
		$sti->execute();

		while ($row = $sti->fetch(PDO::FETCH_ASSOC)) {
			$oid = (int)$row['order_id'];
			if (!isset($idxById[$oid])) continue;
			$orders[$idxById[$oid]]['items'][] = [
				'product_id' => (int)$row['product_id'],
				'sku'        => $row['sku'],
				'name'       => $row['product_name'],
				'qty'        => (int)$row['qty'],
				'unit_price' => (float)$row['unit_price'],
				'subtotal'   => (float)$row['subtotal'],
			];
		}

		return $orders;

	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function count_client_orders($db, $customer_id): int {
	try {
		$query = "SELECT COUNT(*) AS total_orders FROM orders WHERE customer_id = :customer_id";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)$row['total_orders'];
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function fetch_products(PDO $db): array {
	try {
		$query = "SELECT id, name, price FROM products WHERE active=1 ORDER BY name ASC";
		$st = $db->prepare($query);
		$st->execute();
		return $st->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}


function fetch_recent_payments($db, $customer_id) {
	try {
		$query = "SELECT id, order_id, amount, method, payment_date, note, receipt_path
					FROM payments
					WHERE customer_id = :customer_id
					ORDER BY payment_date DESC, id DESC
					LIMIT 5";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}

function fetch_client_active_orders($db, $customer_id) {
	try {
		$query = "SELECT * FROM orders
					WHERE customer_id = :customer_id
					AND (status = 'active' OR status = 'partial')
					ORDER BY id DESC";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException | ErrorException | Exception $e) {
		error_log('Database Error: ' . $e->getMessage());
		throw $e;
	}
}