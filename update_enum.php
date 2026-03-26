<?php
require_once 'backend/config.php';
try {
    $pdo->exec("ALTER TABLE reservas MODIFY COLUMN status ENUM('pendente', 'pago', 'expirado', 'bonus') DEFAULT 'pendente'");
    echo "Reservas status updated.\n";
    $pdo->exec("ALTER TABLE numeros MODIFY COLUMN status ENUM('disponivel', 'reservado', 'pago', 'bonus') DEFAULT 'disponivel'");
    echo "Numeros status updated.\n";
    
    // Convert existing bonus reservations (from my previous step) to status 'bonus'
    $pdo->exec("UPDATE reservas SET status = 'bonus' WHERE is_bonus = 1");
    // Also update associated numbers
    $pdo->exec("UPDATE numeros n JOIN reservas r ON n.reserva_id = r.id SET n.status = 'bonus' WHERE r.is_bonus = 1");
    echo "Existing bonus entries migrated.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
