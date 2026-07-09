<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title> Fabiana Santos - b16238d0</title>
    <?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
    <?php

    if (!isset($_SESSION['email']))
    {
        include_once('without_login.php');
    }
    else
    {
        include_once('view.php');
    }
    ?>
    <!-- Codigo desenvolvido por Fabiana Santos - b16238d0 - Módulo 4 - Webdevelopment for All - Universidade de Michigan -->
</div>
</body>

