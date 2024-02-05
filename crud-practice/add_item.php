<?php
session_start();
include('includes/config.php');

class ItemManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addItem($name, $quantity, $image) {
        $name = mysqli_real_escape_string($this->conn, $name);
    
        // Check if the product with the same name or image already exists
        if ($this->isProductExists($name, $image)) {
            $error = "Product with the same name or image already exists.";
            return;
        }
    
        $uploadResult = $this->uploadImage($image);
    
        if ($uploadResult['success']) {
            $target_file = $uploadResult['target_file'];
    
            $insertItemSql = $this->conn->prepare("INSERT INTO items (name, quantity, image) VALUES (?, ?, ?)");
            // Modify the bind_param to use 'ssi' for the string, string, and integer types
            $insertItemSql->bind_param("ssi", $name, $quantity, $target_file);
    
            if ($insertItemSql->execute()) {
                $success = "Item added successfully.";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error: " . $this->conn->error;
            }
    
            $insertItemSql->close();
        } else {
            $error = $uploadResult['error'];
        }
    }
    
    private function isProductExists($name, $image) {
        $checkProductSql = $this->conn->prepare("SELECT id FROM items WHERE name = ? OR image = ?");
        $checkProductSql->bind_param("ssi", $name, $image);
        $checkProductSql->execute();
        $result = $checkProductSql->get_result();

        return $result->num_rows > 0;
    }

    private function uploadImage($image) {
        $targetDir = "uploads/";
        $imageFileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));

        // Hash the file name to ensure uniqueness
        $hashedFilename = md5(uniqid()) . '.' . $imageFileType;
        $targetFile = $targetDir . $hashedFilename;
        $uploadOk = 1;

        // Check if image file is a valid image
        $check = getimagesize($image["tmp_name"]);
        if ($check === false) {
            return ['success' => false, 'error' => "File is not a valid image."];
        }

        // Check file size
        if ($image["size"] > 5000000) {
            return ['success' => false, 'error' => "Sorry, your file is too large."];
        }

        // Allow only certain file formats
        $allowedFormats = array("jpg", "jpeg", "png", "gif");
        if (!in_array($imageFileType, $allowedFormats)) {
            return ['success' => false, 'error' => "Sorry, only JPG, JPEG, PNG, and GIF files are allowed."];
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return ['success' => false, 'error' => "Sorry, your file was not uploaded."];
        } else {
            // If everything is ok, try to upload file
            if (move_uploaded_file($image["tmp_name"], $targetFile)) {
                return ['success' => true, 'target_file' => $targetFile];
            } else {
                return ['success' => false, 'error' => "Sorry, there was an error uploading your file."];
            }
        }
    }
}

$itemManager = new ItemManager($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $quantity = intval($_POST['quantity']);
    $image = $_FILES["image"];

    $itemManager->addItem($name, $quantity, $image);
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Inventory Item</title>
     <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 80%;
            max-width: 500px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="file"] {
            border: none;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .message, .error {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            padding: 10px;
        }

        @media only screen and (max-width: 600px) {
            form {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/side_navbar.php'); ?>
    <h2>Add Inventory Item</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" required><br>
        <label>Quantity:</label>
        <input type="number" name="quantity" required><br>
        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required><br>
        <button type="submit">Add Item</button>
    </form>
    <?php if(isset($error)) { echo '<div class="error">' . $error . '</div>'; } ?>
<?php if(isset($success)) { echo '<div class="message">' . $success . '</div>'; } ?>
</body>
</html>