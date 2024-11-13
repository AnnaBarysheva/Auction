<?php
session_start();
$link = @include 'db_connect.php';

$connectionError = false;
$error_messages = [];

if (!$link) {
    $connectionError = true;
    $error_messages[] = "Ошибка подключения к серверу. Пожалуйста, попробуйте позже.";
} else {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $userId = $_SESSION['user_id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['paint_name'];
        $size = $_POST['size'];
        $styleId = $_POST['styles'];
        $materialId = $_POST['materials'];
        $year = $_POST['creation_year'];
        $author = $_POST['author'];
        $seller = $_POST['seller'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $lotNumber = $_POST['lot_number'];
        $startingPrice = $_POST['starting_price'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        if (empty($styleId) || empty($materialId)) {
            $error_messages[] = "Ошибка: Выберите стиль и материал для картины.";
        }
        $uploadOk = 1;

        if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image_path']['tmp_name'];
            $fileName = $_FILES['image_path']['name'];
            $fileSize = $_FILES['image_path']['size'];
            $fileType = $_FILES['image_path']['type'];

            $maxFileSize = 2 * 1024 * 1024;
            if ($fileSize > $maxFileSize) {
                $error_messages[] = "Ошибка: Размер файла превышает 2 MB.";
                $uploadOk = 0;
            }

            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                $error_messages[] = "Ошибка: Неверный формат файла. Пожалуйста, загрузите изображение в формате jpg, jpeg, png или gif.";
                $uploadOk = 0;
            }

             // Проверка на битый файл
            if ($uploadOk == 1) {
                // Проверяем, является ли файл валидным изображением
                if (@getimagesize($fileTmpPath) === false) {
                    $error_messages[] = "Файл поврежден или не является изображением.";
                    $uploadOk = 0;
                }
            }

            if (empty($error_messages)) {
                $newFileName = uniqid('painting_', true) . '.' . $fileExtension;
                $uploadFileDir = 'uploads/';
                $destPath = $uploadFileDir . $newFileName;
                if (!is_writable($uploadFileDir)) {
                    $error_messages[] = "Ошибка: Директория 'uploads/' недоступна для записи.";
                }

                if (!move_uploaded_file($fileTmpPath, $destPath)) {
                    $error_messages[] = "Ошибка: Не удалось сохранить файл на сервере. Путь: " . $destPath;
                    $uploadOk = 0;
                
                } else {
                    $imagePath = $destPath;
                }
            }
        } else {
            $error_messages[] = "Ошибка: Файл не загружен или произошла ошибка загрузки.";
            $uploadOk = 0;
        }
        if ($uploadOk == 1) {
        // if (empty($error_messages)) {
            $insertSeller = "INSERT INTO Sellers (full_name, phone, email) VALUES ('$seller', '$phone', '$email')";
            if (mysqli_query($link, $insertSeller)) {
                $sellerId = mysqli_insert_id($link);

                $insertAuction = "INSERT INTO Auctions (start_date, end_date) VALUES ('$startDate', '$endDate')";
                if (mysqli_query($link, $insertAuction)) {
                    $auctionId = mysqli_insert_id($link);

                    $insertPainting = "INSERT INTO Paintings (paint_name, size, id_material, id_style, creation_year, author, image_path, id_seller, id_user) VALUES ('$name', '$size', '$materialId', '$styleId', '$year', '$author', '$imagePath', '$sellerId', '$userId')";
                    if (mysqli_query($link, $insertPainting)) {
                        $paintingId = mysqli_insert_id($link);

                        $insertAuctionPainting = "INSERT INTO PaintingsOnAuction (id_painting, id_auction, lot_number, starting_price) VALUES ('$paintingId', '$auctionId', '$lotNumber', '$startingPrice')";
                        if (mysqli_query($link, $insertAuctionPainting)) {
                            // header('Location: index.php');
                            header("Location:  index.php?upload_success=1");
                            exit();
                        } else {
                            $error_messages[] = "Ошибка при вставке в PaintingsOnAuction: " . mysqli_error($link);
                        }
                    } else {
                        $error_messages[] = "Ошибка при вставке картины: " . mysqli_error($link);
                    }
                } else {
                    $error_messages[] = "Ошибка при вставке аукциона: " . mysqli_error($link);
                }
            } else {
                $error_messages[] = "Ошибка при вставке продавца: " . mysqli_error($link);
            }
        }
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление картины на аукцион</title>
</head>
<body>
<script src="script.js"></script>

<?php if ($connectionError): ?>
    <script>
        alert("<?php echo addslashes($error_messages[0]); ?>");
        window.location.href = "index.php";

    </script>
<?php endif; ?>

<?php if (!empty($error_messages) && !$connectionError): ?>
    <script>
        alert('<?php echo addslashes(implode("\n", $error_messages)); ?>');
        window.location.href = "index.php";
    </script>
<?php endif; ?>

<div id="errorModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeErrorModal" class="close-button">&times;</span>
        <h2>Ошибка</h2>
        <p id="errorMessage"></p>
    </div>
</div>

</body>
</html>
