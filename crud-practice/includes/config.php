<?php
class DatabaseConnection {
    private $conn;

    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Usage example
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crud";

$dbConnection = new DatabaseConnection($servername, $username, $password, $dbname);
$conn = $dbConnection->getConnection();

// ... Your other code ...

// Close the connection when done
$dbConnection->closeConnection();
?>
