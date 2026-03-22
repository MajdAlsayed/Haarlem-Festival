<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Orders for admin export (joins customer when user_id is set).
 */
final class OrderRepository
{
    /**
     * All orders with customer fields and line item count for export.
     *
     * @return list<array<string, mixed>>
     */
    public function getAllForExport(): array
    {
        $db = Database::getConnection();
        $sql = '
            SELECT
                o.order_id,
                o.user_id,
                o.status,
                o.total_amount,
                o.created_at,
                o.paid_at,
                u.email AS customer_email,
                u.first_name AS customer_first_name,
                u.last_name AS customer_last_name,
                (
                    SELECT COUNT(*)
                    FROM order_items oi
                    WHERE oi.order_id = o.order_id
                ) AS line_items_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            ORDER BY o.created_at DESC
        ';
        $stmt = $db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
