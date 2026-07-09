<?php
$password = "Admin123"; // the password you want for the new admin
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo $hashedPassword;
?>