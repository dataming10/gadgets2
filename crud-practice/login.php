<?php
session_start();
include('includes/config.php');

class UserLogin {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function loginUser($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND status = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['is_admin'] = $row['is_admin'];
                header("Location: dashboard.php");
            } else {
                return "Invalid password";
            }
        } else {
            return "Invalid username or password";
        }

        $stmt->close();
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// Usage
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $userLogin = new UserLogin($conn);
    $result = $userLogin->loginUser($username, $password);

    $userLogin->closeConnection();

    if (is_string($result)) {
        $error = $result;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
            width: 300px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        a{
            font-size: 10pt;
            line-height: 32px;
            text-decoration: none;
            color: #000000;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button><br>
        <a href="index.php">Register an account</a>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
</body>
</html>
