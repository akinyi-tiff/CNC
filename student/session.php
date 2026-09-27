<?php

session_start();

header("Content-Type: application/json");

if (isset($_SESSION["student_id"])) {

    $name = $_SESSION["student_name"];

    $name_parts = explode(" ", trim($name));

    if (count($name_parts) >= 2) {

        $initials = strtoupper(
            substr($name_parts[0], 0, 1) .
            substr($name_parts[count($name_parts) - 1], 0, 1)
        );

    } else {

        $initials = strtoupper(
            substr($name, 0, 2)
        );
    }

    echo json_encode([
        "logged_in" => true,
        "name" => $name,
        "initials" => $initials
    ]);

} else {

    echo json_encode([
        "logged_in" => false
    ]);
}