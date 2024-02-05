<?php
include('includes/config.php');

class UserRegistration {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function registerUser($username, $password) {
        $check_result = $this->checkUsernameAvailability($username);

        if ($check_result->num_rows > 0) {
            return "Username already taken. Please choose a different username.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $registrationResult = $this->insertUser($username, $hashedPassword);

            if ($registrationResult === true) {
                return "Registration successful. You can now login.";
            } else {
                return "Error: " . $registrationResult;
            }
        }
    }

    private function checkUsernameAvailability($username) {
        $check_username_sql = "SELECT * FROM users WHERE LOWER(username) = LOWER(?)";
        $check_stmt = $this->conn->prepare($check_username_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();

        return $check_stmt->get_result();
    }

    private function insertUser($username, $hashedPassword) {
        $insert_user_sql = "INSERT INTO users (username, password, is_admin, status) VALUES (?, ?, 1, 0)";
        $insert_stmt = $this->conn->prepare($insert_user_sql);
        $insert_stmt->bind_param("ss", $username, $hashedPassword);

        if ($insert_stmt->execute()) {
            $insert_stmt->close();
            return true;
        } else {
            $error = $insert_stmt->error;
            $insert_stmt->close();
            return $error;
        }
    }

    public function closeConnection() {
        $this->conn->close();
    }
}

// Usage
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $userRegistration = new UserRegistration($conn);
    $result = $userRegistration->registerUser($username, $password);

    $userRegistration->closeConnection();

    if (is_string($result)) {
        $error = $result;
    } else {
        $success = $result;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
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
    <h2>Registration</h2>
    <form method="post" action="">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Register</button><br>
        <a href="login.php">Already have an account?</a>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
