<?php
//session_start();
require_once "pdo.php";

//  if ( ! isset($_SESSION['email']) ) {
//  die('Not logged in');
//}
//    // If the user requested logout go back to index.php
//    if (isset($_POST['logout'])) {
//        header('Location: logout.php');
//        return;
//    }
//    ?>


    <!--//start to view-->
<!DOCTYPE html>
<html>
<head>
    <title> Fabiana Santos - b16238d0</title>
 <?php require_once "bootstrap.php";?>
</head>
<body>
    <div class="container">
        <h1>Tracing Autos for <?php echo $_SESSION['email'];  ?></h1>
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
    <a href="add.php">Add New Entry</a>
    <a href="logout.php">Logout</a>
  <!--  </div>-->
<!--<div>
</div>-->
    <table class="table table-striped table-bordered">
        <h1>Autos</h1>
        <thead>
        <tr>
            <th>Make</th>
            <th>Model</th>
            <th>Year</th>
            <th>Mileage</th>
            <th colspan="2">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM autos /*ORDER BY make*/");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td>' . (htmlentities($row['make'])) . '</td>';
            echo '<td>' . (htmlentities($row['model'])) . '</td>';
            echo '<td>' . (htmlentities($row['year'])) . '</td>';
            echo '<td>' . (htmlentities($row['mileage'])) . '</td>';
            echo '<td>' . '<a href="edit.php?auto_id='.$row['auto_id'].'">Edit</a>  ' . '</td>';
            echo '<td>' . '<a href="delete.php?auto_id='.$row['auto_id'].'">Delete</a>' . '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
   <!-- </form>-->
    <style>
        table tr:last-child {
            font-weight: bold;
        }
        p.confirm {
            color: green;
            text-align: center
        }
    </style>
</div>

</body>
