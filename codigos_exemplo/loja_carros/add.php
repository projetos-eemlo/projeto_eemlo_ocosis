    <?php
    session_start();
    require_once "pdo.php";

   if (!isset($_SESSION['email'])) {
        die('ACCESS DENIED');
    }
    // If the user requested logout go back to index.php
    if (isset($_POST['logout'])) {
        header('Location: logout.php');
        return;
    }
    if (isset($_POST['cancel'])) {
    // Redirect the browser to view.php
    header("Location: view.php");
    return;
}
    //inserted variables - validating not null
        if ((isset($_POST['year']) && isset($_POST['mileage'])) && isset($_POST['make']))
    {
        //validation
        if (strlen($_POST['year']) < 1 || strlen($_POST['mileage']) < 1 || strlen($_POST['make']) < 1 || strlen($_POST['model']) < 1) {
            $_SESSION['error'] = 'All fields are required';
        /*    header("Location: edit.php?auto_id=" . $_POST['auto_id']);*/
            header('Location: add.php');

            return;
        }

        if (!is_numeric($_POST['year']) || !is_numeric($_POST['mileage'])) {
            $_SESSION["error"] = 'Mileage and year must be numeric';
            header('Location: add.php');
            return;
        } else if(empty($_POST['make']))
        {
            $_SESSION["error"] = 'Make is required';
            header('Location: add.php');
            return;
        }
        else
        {
            $stmt = $pdo->prepare('INSERT INTO autos (make, model, year, mileage) VALUES ( :mk, :md, :yr, :mi)');
            $stmt->execute(
                    array(
                        ':mk' => htmlentities($_POST['make']),
                        ':md' => htmlentities($_POST['model']),
                        ':yr' => htmlentities($_POST['year']),
                        ':mi' => htmlentities($_POST['mileage'])
                    )
                );
            $_SESSION["success"] = 'Record added.';
            header('Location: index.php');
            return;
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fabiana Santos - b16238d0</title>
        <?php require_once "bootstrap.php"; ?>
    </head>

    <body>
    <h1>Tracking Autos for <?php echo $_SESSION['email']; ?></h1>
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

<div class="container">
     <div class="row">
        <div class="col-md-3">
                <div class="card" mx-auto  style="width: 25rem;">
                    <form method="POST" class="form1">
                    <div class="form-group">
                        <label for="make">Fabricante</label>
                        <input type="text"  class="form-control" size="40" name="make">
                    </div>
                    <div class="form-group">
                        <label for="model">Modelo</label>
                        <input type="text" class="form-control"  size="40" name="model">
                    </div>
                    <div class="form-group">
                        <label for="year">Ano</label>
                        <input type="text" class="form-control" size="40" name="year">
                    </div>
                    <div class="form-group">
                        <label for="mileage">Quilometragem</label>
                        <input type="text" class="form-control" size="37" name="mileage">
                    </div>
                        <input type="submit" class="btn btn-primary" value="Add" name="Add">
                        <input type="submit" class="btn btn-primary" name="cancel" value="Cancel">
                    </form>
                </div>
        </div>
     </div>
</div>
 
        </body>

    <!--<form class="form-login" method="post">
        <div class="login-card card">
                <div class="card-header">
                    <span class="font-weight-bold"><h1>Tracking Autos for <?php /*echo $_SESSION['email']; */?></h1>
                    </span>
                </div>
                <div class="card-body">
                         <div class="form-group">
                             <label for="make">Make</label>
                             <input type="text" size="40" name="make">
                         </div>
                        <div class="form-group">
                            <label for="year">Year</label>
                            <input type="text" size="40" name="year">
                        </div>
                        <div class="form-group">
                             <label for="mileage">Mileage</label>
                             <input type="text" size="37" name="mileage">
                        </div>
                        <div>
                            <input type="submit" value="Add" name="Add">
                            <input type="submit" name="cancel" value="Cancel">
                        </div>
                </div>
        </div>
    </form>
-->






