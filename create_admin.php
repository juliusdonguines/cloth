<?php
include 'db.php';

$first_name = 'Admin';
$last_name = 'Account';
$email = 'admin@gmail.com';
$address = 'Admin Address';
$contact_number = '0000000000';
$birthday = '2000-01-01';
$role = 'admin';
$password = password_hash('admin', PASSWORD_DEFAULT); // ✅ hashed correctly

// Remove existing admin (optional)
$conn->query("DELETE FROM users WHERE email = 'admin@gmail.com'");

// Insert new admin
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, address, contact_number, birthday, password, role)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssss", $first_name, $last_name, $email, $address, $contact_number, $birthday, $password, $role);

if ($stmt->execute()) {
    echo "✅ Admin account created with password 'admin'.";
} else {
    echo "❌ Error: " . $stmt->error;
}
?>
