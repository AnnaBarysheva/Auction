<?php
//    $link = mysqli_connect("localhost", "root", "alina", "Auction");
// $link = mysqli_connect("localhost", "root", "root_Passwrd132", "Auction");
$link = include 'db_connect.php';

if ($link === false) {
    die(json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных.']));
}

$data = json_decode(file_get_contents("php://input"), true);
$id_painting = $data['id_painting'];

// Start a transaction
mysqli_begin_transaction($link);

try {
    // Delete from PaintingsOnAuction table first
    $sql = "DELETE FROM PaintingsOnAuction WHERE id_painting = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_painting);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Then delete from the Paintings table
    $sql = "DELETE FROM Paintings WHERE id_painting = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_painting);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Commit the transaction
    mysqli_commit($link);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Roll back the transaction in case of error
    mysqli_rollback($link);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($link);
?>