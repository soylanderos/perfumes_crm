<?php
function fetch_all_clients(PDO $db): array {
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