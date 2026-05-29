<?php

require_once "pdo.php";
require_once "bootstrap.php";

session_start();

if (!isset($_SESSION['email']))
{
    die('Not logged in');
}
if ((isset($_POST['year']) && isset($_POST['mileage'])) && isset($_POST['make']) && isset($_POST['model']) && isset($_POST['auto_id'])) {
// Data validation
    if (strlen($_POST['year']) < 1 || strlen($_POST['mileage']) < 1 || strlen($_POST['make']) < 1 || strlen($_POST['model']) < 1) {
        $_SESSION['error'] = 'All fields are required';
        header("Location: edit.php?auto_id=" . $_POST['auto_id']);
        return;
    }
    if (!is_numeric($_POST['year'])) {
        $_SESSION["error"] = 'Year must be numeric';
        header("Location: edit.php?auto_id=" . $_POST['auto_id']);
        return;
    }
    if (!is_numeric($_POST['mileage'])) {
        $_SESSION["error"] = 'Mileage must be numeric';
        header("Location: edit.php?auto_id=" . $_POST['auto_id']);
        return;
    }
    $sql = "UPDATE autos SET 
                make = :make,
                model = :model, 
                year = :year, 
                mileage = :mileage
            WHERE auto_id = :auto_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array
        (
        ':make' => $_POST['make'],
        ':model' => $_POST['model'],
        ':year' => $_POST['year'],
        ':mileage' => $_POST['mileage'],
        ':auto_id' => $_POST['auto_id'])
        );
    $_SESSION['success'] = 'Record updated';
    header( 'Location: index.php' ) ;
    return;
}
if (!isset($_GET['auto_id']) ) {
    $_SESSION['error'] = "Missing auto_id";
    header('Location: index.php');
    return;
}
$stmt = $pdo->prepare("SELECT * FROM autos where auto_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['auto_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for auto_id';
    header( 'Location: index.php' ) ;
    return;
}

$mk = htmlentities($row['make']);
$mo = htmlentities($row['model']);
$y = htmlentities($row['year']);
$mi = htmlentities($row['mileage']);
$auto_id = $row['auto_id'];
?>

<h1>Editing Automobile</h1>
<?php
// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
?>
<form method="post">
    <p>Make:
        <input type="text" name="make" value="<?= $mk ?>"></p>
    <p>Model:
        <input type="text" name="model" value="<?= $mo ?>"></p>
    <p>Year:
        <input type="text" name="year" value="<?= $y ?>"></p>
    <p>Mileage:
        <input type="text" name="mileage" value="<?= $mi ?>"></p>
    <input type="hidden" name="auto_id" value="<?= $auto_id ?>">
    <p><input type="submit" value="Save"/>
        <a href="index.php">Cancel</a></p>
</form>
