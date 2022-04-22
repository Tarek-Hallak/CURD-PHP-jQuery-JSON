<?php
require_once "pdo.php";
require_once "Head.php";
require_once "utilities.php";

session_start();

$sql = "SELECT * FROM PROFILE";
$stmt = $pdo->query($sql);
$data = $stmt->fetchAll();

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
    <h1>Resume Registry</h1>
    <?php

    if (!isset($_SESSION['user_name'])) {
        echo ' <p> <a href="login.php" class="btn btn-dark mt-2"> Please log in</a> </p>';
    } else {
        echo ' <p> <a href="logout.php" class="btn btn-dark mt-2"> Logout </a></p>';
    }
    ?>


    <?php
    if ($data == false) {
        echo "No rows found";
    } else {
    ?>
    <form action="">
        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Headline</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <?php
            foreach ($data as $row) {
                ?>
                <tr>
                    <td>
                        <a href="view.php?profile_id=<?= $row['profile_id'] ?>"
                           style="text-decoration: none; color: cadetblue">
                            <?php echo (htmlentities($row['first_name'])) . ' ' . (htmlentities($row['last_name'])) ?>
                        </a>
                    </td>
                    <td><?php echo(htmlentities($row['headline'])) ?></td>
                    <td>
                        <a href="edit.php?profile_id=<?= $row['profile_id'] ?>" class="btn btn-primary">Edit</a>
                        <a href="delete.php?profile_id=<?= $row['profile_id'] ?>" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            <?php }
            } ?>
        </table>

        <?php
        if (isset($_SESSION['user_name'])) {
            echo '<p> <a href="add.php" class="btn btn-dark mt-2">Add New Entry</a> </p>';
        }
        ?>
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
