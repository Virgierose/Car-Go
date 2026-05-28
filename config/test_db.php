<?php
$conn = mysqli_connect(
    'auth-db1515.hstgr.io',
    'u970217706_cargo',
    'a4b3c2d1_GMP',
    'u970217706_cargo',
    3306
);

if (!$conn) {
    echo "Error " . mysqli_connect_errno() . ": " . mysqli_connect_error();
} else {
    echo "Connected successfully!";
    mysqli_close($conn);
}