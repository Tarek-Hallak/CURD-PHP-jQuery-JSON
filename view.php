<?php
require_once "pdo.php";
require_once "head.php";
require_once "utilities.php";
session_start();

is_Logged();


//Make sure that user_id is present
if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

//Make sure that profile_id is correct and found
$proSELECT = "SELECT * FROM profile where profile_id = :pid";
$proSTMT = $pdo->prepare($proSELECT);
$proSTMT->execute(array(":pid" => $_GET['profile_id']));
$proData = $proSTMT->fetch(PDO::FETCH_ASSOC);
if ($proData === false) {
    $_SESSION['error'] = 'Could not load profile!';
    header('Location: index.php');
    return;
}

$eduSELECT = "SELECT edu.year, ins.name 
           FROM education AS edu 
           left join institution AS ins 
           ON edu.institution_id = ins.institution_id
           WHERE edu.profile_id = :pid";
$eduSTMT = $pdo->prepare($eduSELECT);
$eduSTMT->execute(array(":pid" => $_GET['profile_id']));
$eduData = $eduSTMT->fetchAll(PDO::FETCH_ASSOC);

$posSELECT = "SELECT * FROM positions
           where profile_id = :pid";
$posSTMT = $pdo->prepare($posSELECT);
$posSTMT->execute(array(":pid" => $_GET['profile_id']));
$posData = $posSTMT->fetchAll(PDO::FETCH_ASSOC);

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
        <h2>Profile information</h2>
    </div>
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
                    foreach ($eduData as $row) {
                        ?>
                        <div class="card mt-2">
                            <div class="card-body rounded">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="form-label">Year:</label>
                                        <input type="text" class="form-control" readonly
                                               value="<?= htmlentities($row['year']) ?>">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label">School:</label>
                                        <input type="text" class="form-control" readonly
                                               value="<?= htmlentities($row['name']) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    } ?>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header cardHeader"><h4>Position</h4></div>
            <div class="card-body cardContent rounded-bottom">
                <div id="posContent">
                    <?php
                    foreach ($posData as $row) {
                        ?>
                        <div class="card mt-2">
                            <div class="card-body rounded">
                                <div class="mb-3">
                                    <label class="form-label">Date:</label>
                                    <input type="text" class="form-control" readonly
                                           value="<?= htmlentities($row['date']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description:</label>
                                    <textarea class="form-control"
                                              readonly><?= htmlentities($row['description']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        <?php
                    } ?>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-around mt-5">
            <a href="index.php" class="btn btn-light px-4 col-s-2">Done</a>
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

</body>
</html>


