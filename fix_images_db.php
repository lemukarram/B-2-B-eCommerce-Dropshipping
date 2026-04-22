<?php
define('BASE_PATH', __DIR__ . '/src');
require_once __DIR__ . '/src/core/Database.php';
try {
    \$pdo = \Core\Database::getInstance();
    \$stmt = \$pdo->prepare(\"UPDATE product_images SET image_path = CONCAT('/uploads/', image_path) WHERE image_path NOT LIKE '/uploads/%'\");
    \$stmt->execute();
    echo \"UPDATED: \" . \$stmt->rowCount();
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
