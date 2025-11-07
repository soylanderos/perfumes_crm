<?php
function fetch_products($db) {
    try {
        $query = "SELECT *
                FROM products
                ORDER BY created_at DESC, id DESC";
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException | ErrorException | Exception $e) {
        error_log('Database Error: ' . $e->getMessage());
        throw $e;
    }
}