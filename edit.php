<?php
require_once "pdo.php";
require_once "Head.php";
require_once "utilities.php";
session_start();
is_Logged();

//Make sure that user_id is present
if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

$ProSel = "SELECT * FROM profile where profile_id = :pid";
$proSTMT = $pdo->prepare($ProSel);
$proSTMT->execute(array(":pid" => $_GET['profile_id']));
$proData = $proSTMT->fetch(PDO::FETCH_ASSOC);

//Make sure that profile_id is correct and found
if ($proData === false) {
    $_SESSION['error'] = 'ID Not found!';
    header('Location: index.php');
    return;
}
$eduSel = "SELECT edu.year, ins.name 
              FROM education AS edu 
              left join institution AS ins 
              ON edu.institution_id = ins.institution_id
              WHERE edu.profile_id = :pid";
$eduSTMT = $pdo->prepare($eduSel);
$eduSTMT->execute(array(":pid" => $_GET['profile_id']));
$eduData = $eduSTMT->fetchAll(PDO::FETCH_ASSOC);

$PosSel = "SELECT * FROM positions where profile_id = :pid";
$posSTMT = $pdo->prepare($PosSel);
$posSTMT->execute(array(":pid" => $_GET['profile_id']));
$posData = $posSTMT->fetchAll(PDO::FETCH_ASSOC);


//check if the profile is belonging to the logged in user
if (!($proData['user_id'] == $_SESSION['user_id'])) {
    $_SESSION['error'] = 'You can not access this profile!';
    header('Location: index.php');
    return;
}


if (isset($_POST['save'])) {

    //Profile validate
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 ||
        strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = 'All fields are required';
        header("Location: edit.php?profile_id=" . $_GET['profile_id']);
        return;
    }
    if (strpos($_POST['email'], '@') != true) {
        $_SESSION["error"] = "Email must have an at-sign (@)";
        header("Location: edit.php?profile_id=" . $_GET['profile_id']);
        return;
    }
    //Eduction validate
    for ($i = 0; $i < 1000; $i++) {
        if (!isset($_POST['year-' . $i])) continue;

        if (strlen($_POST['year-' . $i]) < 1 || strlen($_POST['school-' . $i]) < 1) {
            $_SESSION['error'] = 'All Eduction fields are required';
            header("Location: edit.php?profile_id=" . $_GET['profile_id']);
            return;
        }
        if (!is_numeric($_POST['year-' . $i])) {
            $_SESSION['error'] = 'Eduction Year must be a number!';
            header("Location: edit.php?profile_id=" . $_GET['profile_id']);

            return;
        }
    }

    //Position validate
    for ($i = 0; $i < 1000; $i++) {
        if (!isset($_POST['date-' . $i])) continue;

        if (strlen($_POST['date-' . $i]) < 1 || strlen($_POST['des-' . $i]) < 1) {
            $_SESSION['error'] = 'All Position fields are required';
            header("Location: edit.php?profile_id=" . $_GET['profile_id']);
            return;
        }
        if (!is_numeric($_POST['date-' . $i])) {
            $_SESSION['error'] = 'Position Year must be a number!';
            header("Location: edit.php?profile_id=" . $_GET['profile_id']);
            return;
        }
    }


    try {
        //Update Data for this Profile
        $proUp = "UPDATE profile SET user_id = :uid,
                                      first_name = :fn,
                                      last_name = :ln,
                                      email = :em,
                                      headline = :he,
                                      summary = :su
                    WHERE profile_id = :pid";
        $proSTMT = $pdo->prepare($proUp);
        $proSTMT->execute(array(
                ':pid' => $_POST['profile_id'],
                ':uid' => $_SESSION['user_id'],
                ':fn' => $_POST['first_name'],
                ':ln' => $_POST['last_name'],
                ':em' => $_POST['email'],
                ':he' => $_POST['headline'],
                ':su' => $_POST['summary'])
        );

        //Querying on Education CARDS
        $eduDel = "DELETE FROM education WHERE profile_id = :pid";
        $posSTMT = $pdo->prepare($eduDel);
        $posSTMT->execute(array(':pid' => $_POST['profile_id']));
        $rate = 0;
        for ($i = 0; $i < 100; $i++) {
            if (!isset($_POST['school-' . $i])) continue;
            $insID = checkSchool($pdo, $i);

            //Inserting Data into Education table
            $eduSQL = "INSERT INTO education (profile_id,institution_id,rank,year)
            VALUES (:pid , :inst , :ra , :ye)";
            $eduSTMT = $pdo->prepare($eduSQL);
            $eduSTMT->execute(array(
                ':pid' => $_POST['profile_id'],
                ':inst' => $insID,
                ':ra' => $rate,
                ':ye' => $_POST['year-' . $i]
            ));
            $rate++;
        }

        //Querying on Positions CARDS
        $posDel = "DELETE FROM positions WHERE profile_id = :pid";
        $posSTMT = $pdo->prepare($posDel);
        $posSTMT->execute(array(':pid' => $_POST['profile_id']));
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_POST['des-' . $i])) continue;

            //Inserting Data into Position table
            $posSel = "INSERT INTO positions (profile_id,date,description)
                       VALUES (:pid ,:da ,:des)";
            $posSTMT = $pdo->prepare($posSel);
            $posSTMT->execute(array(
                ':pid' => $_POST['profile_id'],
                ':da' => $_POST['date-' . $i],
                ':des' => $_POST['des-' . $i]
            ));
        }

        $_SESSION['success'] = 'Record Updated!';
        header('Location: index.php');
        return;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Updated failed: ' . $e;
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
    <?php Msg(); ?>
    <div class="card-header">
        <h2>Editing Profile </h2>
    </div>
    <form method="POST">
        <div class="card-body">
            <div class="card">
                <div class="card-header cardHeader"><h4>Profile</h4></div>
                <div class="card-body cardContent rounded-bottom">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">First Name:</label>
                            <input type="text" class="form-control" id="id_1" name="first_name"
                                   value="<?= htmlentities($proData['first_name']) ?>" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Last Name:</label>
                            <input type="text" class="form-control" id="id_2" name="last_name"
                                   value="<?= htmlentities($proData['last_name']) ?>" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Email:</label>
                            <input type="text" class="form-control" id="id_3" name="email"
                                   value="<?= htmlentities($proData['email']) ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Headline:</label>
                            <input type="text" class="form-control" id="id_4" name="headline"
                                   value="<?= htmlentities($proData['headline']) ?>">
                        </div>
                        <div class="col">
                            <label class="form-label">Summary:</label>
                            <textarea class="form-control" id="id_5"
                                      name="summary"><?= htmlentities($proData['summary']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header cardHeader"><h4>Education</h4></div>
                <div class="card-body cardContent rounded-bottom">
                    <div class="right">
                        <button class="btn btn-success" id="addEdu">+</button>
                    </div>
                    <div id="eduContent">
                        <?php
                        $eduCount = 0;
                        foreach ($eduData as $row) {
                            ?>
                            <div class="card mt-2 " id="eduItem-<?= $eduCount ?>">
                                <div class="card-body rounded">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label class="form-label">Year:</label>
                                            <input type="text" class="form-control" id="year-<?= $eduCount ?>"
                                                   name="year-<?= $eduCount ?>"
                                                   value="<?= htmlentities($row['year']) ?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="form-label school">School:</label>
                                            <input type="text" class="form-control" id="school-<?= $eduCount ?>"
                                                   name="school-<?= $eduCount ?>"
                                                   value="<?= htmlentities($row['name']) ?>">
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-danger"
                                                    onclick="$('#eduItem-<?= $eduCount ?>').remove(); return false;">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $eduCount++;
                        } ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header cardHeader"><h4>Position</h4></div>
                <div class="card-body cardContent rounded-bottom">
                    <div class="right">
                        <button class="btn btn-success" id="addPos">+</button>
                    </div>
                    <div id="posContent">
                        <?php
                        $posCount = 0;
                        foreach ($posData

                        as $row) {
                        ?>
                        <div class="card mt-2" id="posItem-<?= $posCount; ?>">
                            <div class="card-body rounded">
                                <div class="mb-3">
                                    <label class="form-label">Date:</label>
                                    <input type="text" class="form-control" id="date-<?= $posCount; ?>"
                                           name="date-<?= $posCount; ?>" value="<?= htmlentities($row['date']) ?>">
                                </div>
                                <div
                                <label class="form-label">Description:</label>
                                <textarea
                                        class="form-control" id="des-<?= $posCount; ?>"
                                        name="des-<?= $posCount; ?>"><?= htmlentities($row['description']) ?></textarea>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-danger"
                                        onclick="$('#posItem-<?= $posCount; ?>').remove();">Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                    $posCount++;
                    } ?>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-around mt-5">
            <input type="hidden" value="<?= $proData['profile_id'] ?>" name="profile_id">
            <button type="submit" class="btn btn-success px-4 col-s-2" onclick="return addValidate();" name="save">
                Save
            </button>
            <button type="submit" class="btn btn-dark px-4 col-s-2" name="cancel">Cancel</button>
        </div>
    </form>
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
            <div class="form-group col-md-6">
                <label class="form-label">Year:</label>
                <input type="text" class="form-control" id="year-@COUNT@" name="year-@COUNT@">
            </div>
            <div class="form-group col-md-6">
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
        <div class="mb-3">
            <label class="form-label">Date:</label>
            <input type="text" class="form-control" id="date-@COUNT@" name="date-@COUNT@">
        </div>
        <div>
            <label class="form-label">Description:</label>
            <textarea class="form-control" id="des-@COUNT@" name="des-@COUNT@"></textarea>
        </div>
        <div class="mt-3">
            <button class="btn btn-danger" onclick="$('#posItem-@COUNT@').remove();">Delete</button>
        </div>
    </div>
</div>
</script>

</body>
</html>
