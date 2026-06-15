<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "amsadesign_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to " . $dbname . "<br>";

$sql = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($sql);

if ($result) {
  $row = $result->fetch_assoc();
  echo "Products count: " . $row['count'] . "<br>";
} else {
  echo "Error selective count: " . $conn->error . "<br>";
  // Try to check if table exists in engine
  $sql = "SHOW TABLES LIKE 'products'";
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
    echo "Table 'products' exists in list but maybe not in engine.<br>";
  } else {
    echo "Table 'products' does not exist in list.<br>";
  }
}

$conn->close();
?>
