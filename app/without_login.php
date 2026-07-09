

    <h1>Welcome to Automobiles Database</h1>
    <?php
    if (isset($_SESSION["error"])) {
        echo('<p style="color:red">' . $_SESSION["error"] . "</p>\n");
        unset($_SESSION["error"]);
    }
    if (isset($_SESSION["success"])) {
        echo('<p style="color:green">' . $_SESSION["success"] . "</p>\n");
        unset($_SESSION["success"]);
    }
    ?>
    <p>
        <a href="login.php">Please log in</a>
    </p>
    <p>Attempt to go to <a href="add.php">add data</a> without logging in</p>
