<?php
require_once __DIR__ . '/config/config.php';
try {
    $db = Database::getConnection();
    $db->query("ALTER TABLE users ADD COLUMN photo VARCHAR(255) DEFAULT NULL");
    echo "Added photo column successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
