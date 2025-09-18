<?php
$hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
echo $hashedPassword;
?>