<?php
require_once "pdo.php";
require_once "Head.php";
require_once "utilities.php";
session_start();

is_Logged();

if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

if (isset($_POST['add'])) {
    //**Data validation Start****************
    //Profile validate
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 ||
        strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = 'All fields are required';
        header("Location: add.php");
        return;
    }
    if (strpos($_POST['email'], '@') != true) {
        $_SESSION["error"] = "Email must have an at-sign (@)";
        header('Location: add.php');
        return;
    }
    //Eduction validate
    for ($i = 0; $i < 100; $i++) {
        if (!isset($_POST['year-' . $i])) continue;

        if (strlen($_POST['year-' . $i]) < 1 || strlen($_POST['school-' . $i]) < 1) {
            $_SESSION['error'] = 'All Eduction fields are required';
            header("Location: add.php");
            return;
        }
        if (!is_numeric($_POST['year-' . $i])) {
            $_SESSION['error'] = 'Eduction Year must be a number!';
            header("Location: add.php");
            return;
        }
    }
    //Position validate
    for ($i = 0; $i < 100; $i++) {
        if (!isset($_POST['date-' . $i])) continue;

        if (strlen($_POST['date-' . $i]) < 1 || strlen($_POST['desc-' . $i]) < 1) {
            $_SESSION['error'] = 'All Position fields are required';
            header("Location: add.php");
            return;
        }
        if (!is_numeric($_POST['date-' . $i])) {
            $_SESSION['error'] = 'Position Year must be a number!';
            header("Location: add.php");
            return;
        }
    }
    //**Data validation End******************

    try {
        //Inserting Data into Profile table
        $proSQL = "INSERT INTO profile (user_id , first_name, last_name, email , headline, summary)
              VALUES (:uid, :fn, :ln, :em, :he, :su)";
        $proSTMT = $pdo->prepare($proSQL);
        $proSTMT->execute(array(
                ':uid' => $_SESSION['user_id'],
                ':fn' => $_POST['first_name'],
                ':ln' => $_POST['last_name'],
                ':em' => $_POST['email'],
                ':he' => $_POST['headline'],
                ':su' => $_POST['summary'])
        );
        $proID = $pdo->lastInsertId();

        //Querying on Education CARDS
        $rate = 0;
        for ($i = 0; $i < 100; $i++) {
            if (!isset($_POST['school-' . $i])) continue;

            $insID = checkSchool($pdo , $i);

            //Inserting Data into Education table
            $eduSQL = "INSERT INTO education (profile_id,institution_id,rank,year)
            VALUES (:pid , :inst , :ra , :ye)";
            $eduSTMT = $pdo->prepare($eduSQL);
            $eduSTMT->execute(array(
                ':pid' => $proID,
                ':inst' => $insID,
                ':ra' => $rate,
                ':ye' => $_POST['year-' . $i]
            ));
            $rate++;
        }

        //Querying on Positions CARDS
        for ($i = 0; $i < 100; $i++) {
            if (!isset($_POST['desc-' . $i])) continue;

            //Inserting Data into Position table
            $posSQL = "INSERT INTO positions (profile_id,date,description)
                       VALUES (:pid,:da , :desc)";
            $posSTMT = $pdo->prepare($posSQL);
            $posSTMT->execute(array(
                ':pid' => $proID,
                ':da' => $_POST['date-' . $i],
                ':desc' => $_POST['desc-' . $i]
            ));
        }
        $_SESSION['success'] = 'Record Added';
        header('Location: index.php');
        return;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Insert failed' . $e;
        error_log("Insert failed ERROR: " . $e . " /USER: " . $_SESSION["name"]);
        header('Location: index.php');
        return;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<body style="background-color: gray">
<?php Msg(); ?>

<!----------------START NavBar------------------------------------------------------------->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-md">
        <a class="navbar-brand" href="#">CURD Agency</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <?php echo htmlentities($_SESSION['user_name']) ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="logout.php">Log out</a></li>
                    </ul>
                </li>
            </ul>
            <form class="d-flex" method='POST'>
                <input type="search" name="find_Id" class="form-control me-2" placeholder="Search By ID"
                       aria-label="Search">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </form>
        </div>
    </div>
</nav>

<!----------------START Body------------------------------------------------------------->
<div class="container mt-3">
    <div class="card">
        <div class="card-header">
            <h2>Adding Profile for <?= htmlentities($_SESSION['user_name']) ?></h2>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="card">
                    <div class="card-header cardHeader"><h4>Profile</h4></div>
                    <div class="card-body cardContent rounded-bottom">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">First Name:</label>
                                <input type="text" class="form-control" id="id_1" name="first_name">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">Last Name:</label>
                                <input type="text" class="form-control" id="id_2" name="last_name">
                            </div>
                            <div class="form-group col-md-6 mt-2">
                                <label class="form-label">Email:</label>
                                <input type="text" class="form-control" id="id_3" name="email">
                            </div>
                            <div class="form-group col-md-6 mt-2">
                                <label class="form-label">Headline:</label>
                                <input type="text" class="form-control" id="id_4" name="headline">
                            </div>
                            <div class="form-group col mt-2">
                                <label class="form-label ">Summary:</label>
                                <textarea class="form-control" id="id_5" name="summary"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header cardHeader"><h4>Education</h4></div>
                    <div class="card-body cardContent rounded-bottom">
                        <div class="right">
                            <button class="btn btn-success px-4 mb-2" id="addEdu">+</button>
                        </div>
                        <div id="eduContent">
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header cardHeader"><h4>Position</h4></div>
                    <div class="card-body cardContent rounded-bottom">
                        <div class="right">
                            <button class="btn btn-success px-4 mb-2" id="addPos">+</button>
                        </div>
                        <div id="posContent">
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-around mt-5">
                    <input type="submit" class="btn btn-success px-4 col-s-2" value="Add"
                           onclick="return addValidate();" name="add">
                    <input type="submit" class="btn btn-light px-4 col-s-2" value="Cancel" name="cancel">
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<div>
    <hr>
    <br> <br>
</div>
<!----------------START footer------------------------------------------------------------->
<footer class="footer fixed-bottom bg-dark ">
    <a href="mailto:Tarek.K.hallak@gmail.com" class="text-reset">
        <p class="row justify-content-center mt-3 text-white"> &copy; By Tarek AL-Hallak</p>
    </a>
</footer>


<!--Cards template---------------------->
<script id="eduTemplate" type="text">
<div class="card mt-2" id="eduItem-@COUNT@">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Year:</label>
                <input type="text" class="form-control" id="year-@COUNT@" name="year-@COUNT@">
            </div>
            <div class="col-md-6">
                <label class="form-label">School:</label>
                <input type="text" class="form-control schools" id="school-@COUNT@" name="school-@COUNT@">
            </div>
            <div class="mt-3">
                <button class="btn btn-danger" onclick="$('#eduItem-@COUNT@').remove(); return false;">Delete</button>
            </div>
        </div>
    </div>
</div>
</script>

<script id="posTemplate" type="text">
<div class="card mt-2" id="posItem-@COUNT@">
    <div class="card-body">
        <div>
            <label class="form-label">Date:</label>
            <input type="text" class="form-control" id="date-@COUNT@" name="date-@COUNT@">
        </div>
        <div>
            <label class="form-label">Description:</label>
            <textarea class="form-control" id="desc-@COUNT@" name="desc-@COUNT@"></textarea>
        </div>
        <div class="mt-3">
            <button class="btn btn-danger" onclick="$('#posItem-@COUNT@').remove();">Delete</button>
        </div>
    </div>
</div>
</script>

</body>
</html>

