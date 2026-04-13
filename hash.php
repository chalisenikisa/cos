<?php
$username = 'admin';
$password = 'admin123';

echo "Username: " . $username . "\n";
echo "Password: " . $password . "\n";
echo "Hash: " . password_hash($password, PASSWORD_DEFAULT) . "\n";