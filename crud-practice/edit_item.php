<?php
include('includes/config.php');

class ItemEditor {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function editItem($id, $name, $quantity, $removeImage, $imageFile) {
        $fetchItemSql = $this->conn->prepare("SELECT * FROM items WHERE id = ?");
        $fetchItemSql->bind_param("i", $id);
        $fetchItemSql->execute();

        $result = $fetchItemSql->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $fetchItemSql->close();

            if ($removeImage) {
                $this->removeImageAndUpdateItem($name, $quantity, $id);
            } else {
                $this->updateItemWithImage($name, $quantity, $id, $imageFile);
            }
        } else {
            $error = "Item not found.";
        }
    }

    private function removeImageAndUpdateItem($name, $quantity, $id) {
        $removeImageSql = $this->conn->prepare("UPDATE items SET name = ?, quantity = ?, image = NULL WHERE id = ?");
        $removeImageSql->bind_param("ssi", $name, $quantity, $id);
        $removeImageSql->execute();

        if ($removeImageSql->affected_rows > 0) {
            $this->redirectToDashboard("Item updated successfully.");
        } else {
            $error = "Error updating item: " . $this->conn->error;
        }

        $removeImageSql->close();
    }

    private function updateItemWithImage($name, $quantity, $id, $imageFile) {
        if ($imageFile && $imagePath = $this->uploadImage($imageFile)) {
            $updateItemSql = $this->conn->prepare("UPDATE items SET name = ?, quantity = ?, image = ? WHERE id = ?");
            $updateItemSql->bind_param("sssi", $name, $quantity, $imagePath, $id);
        } else {
            $updateItemSql = $this->conn->prepare("UPDATE items SET name = ?, quantity = ? WHERE id = ?");
            $updateItemSql->bind_param("ssi", $name, $quantity, $id);
        }

        $updateItemSql->execute();

        if ($updateItemSql->affected_rows > 0) {
            $this->redirectToDashboard("Item updated successfully.");
        } else {
            $error = "Error updating item: " . $this->conn->error;
        }

        $updateItemSql->close();
    }

    private function uploadImage($file) {
        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        // Hash the file name to ensure uniqueness
        $hashedFilename = md5(uniqid()) . '.' . $imageFileType;
        $targetFile = $targetDir . $hashedFilename;

        // Check if the image file is a valid image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return false;
        }

        // Check file size
        if ($file["size"] > 50000000) {
            return false;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            return false;
        }

        // If everything is ok, try to upload file
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            return $targetFile;
        } else {
            return false;
        }
    }

    private function redirectToDashboard($message) {
        $success = $message;
        header("Location: dashboard.php");
        exit();
    }
}

$itemEditor = new ItemEditor($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $removeImage = isset($_POST['remove_image']) ? intval($_POST['remove_image']) : 0;

    $itemEditor->editItem($id, $name, $quantity, $removeImage, $_FILES['image']);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $fetchItemSql = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $fetchItemSql->bind_param("i", $id);
    $fetchItemSql->execute();

    $result = $fetchItemSql->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $quantity = $row['quantity'];
    } else {
        $error = "Item not found.";
    }

    $fetchItemSql->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Inventory Item</title>
    <style>
     body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="file"] {
            margin-top: 5px;
        }

        input[type="checkbox"] {
            vertical-align: middle;
            margin-right: 5px;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12pt;
        }

        a{
            background-color: darkred;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: #ff0000;
            margin-top: 10px;
        }

        .success {
            color: #008000;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            form {
                width: 80%;
            }
        }
    </style>
</head>
<body>
    <h2>Edit Inventory Item</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required><br>
        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?php echo $quantity; ?>" required><br>
        <label>Change Image:</label>
        <input type="file" name="image"><br>
        <label>Remove Image: </label>
        <input type="checkbox" name="remove_image" value="1"><br>
        <button type="submit" name="submit">Update Item</button>
        <a href="dashboard.php"  onclick="return confirm('Are you sure you want to discard this edit?')">Discard Edit</a>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
