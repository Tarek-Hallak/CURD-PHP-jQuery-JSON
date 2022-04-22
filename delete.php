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

$proSel = "SELECT * FROM profile where profile_id = :pid";
$proSTMT = $pdo->prepare($proSel);
$proSTMT->execute(array(":pid" => $_GET['profile_id']));
$proData = $proSTMT->fetch(PDO::FETCH_ASSOC);

//Make sure that profile_id is correct and found
if ($proData === false) {
    $_SESSION['error'] = 'ID Not found!';
    header('Location: index.php');
    return;
}

//check if the profile is belonging to the logged in user
if (!($proData['user_id'] == $_SESSION['user_id'])) {
    $_SESSION['error'] = 'You can not access this profile!';
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

$posSel = "SELECT * FROM positions where profile_id = :pid";
$posSTMT = $pdo->prepare($posSel);
$posSTMT->execute(array(":pid" => $_GET['profile_id']));
$posData = $posSTMT->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['delete']) && isset($_POST['profile_id'])) {
    try {
        $proDel = "DELETE FROM profile WHERE profile_id = :pid";
        $proSTMT = $pdo->prepare($proDel);
        $proSTMT->execute(array(':pid' => $_POST['profile_id']));

        $eduDel = "DELETE FROM education WHERE profile_id = :pid";
        $posSTMT = $pdo->prepare($eduDel);
        $posSTMT->execute(array(':pid' => $_POST['profile_id']));

        $posDel = "DELETE FROM positions WHERE profile_id = :pid";
        $posSTMT = $pdo->prepare($posDel);
        $posSTMT->execute(array(':pid' => $_POST['profile_id']));

        $_SESSION['success'] = 'Record Deleted';
        header('Location: index.php');
        return;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Delete failed';
        error_log("Delete failed ERROR: " . $e . " /USER: " . $_SESSION["name"]);
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
                                   value="<?= htmlentities($proData['first_name']) ?>" disabled>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label">Last Name:</label>
                            <input type="text" class="form-control" id="id_2" name="last_name"
                                   value="<?= htmlentities($proData['last_name']) ?>" disabled>
                        </div>
                        <div class="form-group col-md-6 mt-2">
                            <label class="form-label">Email:</label>
                            <input type="text" class="form-control" id="id_3" name="email"
                                   value="<?= htmlentities($proData['email']) ?>" disabled>
                        </div>
                        <div class="form-group col-md-6 mt-2">
                            <label class="form-label">Headline:</label>
                            <input type="text" class="form-control" id="id_4" name="headline"
                                   value="<?= htmlentities($proData['headline']) ?>" disabled>
                        </div>
                        <div class="form-group col mt-2">
                            <label class="form-label">Summary:</label>
                            <textarea class="form-control" id="id_5"
                                      name="summary" disabled><?= htmlentities($proData['summary']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header cardHeader"><h4>Education</h4></div>
                <div class="card-body cardContent rounded-bottom">
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
                                                   value="<?= htmlentities($row['year']) ?>" disabled>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="form-label school">School:</label>
                                            <input type="text" class="form-control" id="school-<?= $eduCount ?>"
                                                   name="school-<?= $eduCount ?>"
                                                   value="<?= htmlentities($row['name']) ?>" disabled>
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
                                           name="date-<?= $posCount; ?>" value="<?= htmlentities($row['date']) ?>" disabled>
                                </div>
                                <div
                                <label class="form-label">Description:</label>
                                <textarea
                                        class="form-control" id="des-<?= $posCount; ?>"
                                        name="des-<?= $posCount; ?>" disabled><?= htmlentities($row['description']) ?></textarea>
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
            <input type="submit" class="btn btn-danger px-4 col-s-2" value="Delete" name="delete">
            <a href="index.php" class="btn btn-dark px-4 col-s-2"">Cancel</a>
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

</body>
</html>
