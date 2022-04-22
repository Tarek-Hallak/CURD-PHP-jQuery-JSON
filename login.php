<?php
require_once "pdo.php";
require_once "Head.php";
require_once "utilities.php";
session_start();

if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}


if (isset($_POST['login'])) {
    unset($_SESSION['user_name']);
    unset($_SESSION['user_emil']);
    unset($_SESSION['user_id']); // Logout current user

    if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION["error"] = "Email and password are required";
        header('Location: login.php');
        return;
    } else if (strpos($_POST['email'], '@') != true) {
        $_SESSION["error"] = "Email must have an at-sign (@)";
        header('Location: login.php');
        return;
    }
    $salt = 'XyZzy12*_';
    $check = hash('md5', $salt . $_POST['pass']);

    $sql = "SELECT user_id,name FROM users 
            WHERE email= :em AND  password= :pw";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':em' => $_POST['email'],
        ':pw' => hash('md5', $salt . $_POST['pass'])
    ));
    $data = $stmt->fetch();

    if ($data !== false) {
        error_log("Login success :" . $data['name']);
        $_SESSION["user_name"] = $data['name'];
        $_SESSION["user_emil"] = $_POST['email'];
        $_SESSION["user_id"] = $data['user_id'];
        $_SESSION["success"] = "Logged in Successfully";
        header('Location: index.php');
        return;
    } else {
        $_SESSION["error"] = "Incorrect password";
        header('Location: login.php');
        return;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<body style="font-family: sans-serif;">
<?php Msg(); ?>
<!----------------START navbar------------------------------------------------------------->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-md">
        <a class="navbar-brand">CURD Agency</a>
    </div>
</nav>

<!----------------START body------------------------------------------------------------->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 card p-2">
            <div class="card-header p-3">
                Login
            </div>
            <form method="POST">
                <div class="form-floating mb-3 mt-3">
                    <input type="email" class="form-control" name="email" id="id_1722" placeholder="name@amescom.com"
                           autocomplete="username">
                    <label class="form-label">Email address</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" name="pass" id="id_1723"
                           placeholder="Password" utocomplete="current-password">
                    <label class="form-label">Password</label>
                </div>
                <div class="d-flex align-items-center justify-content-around">
                    <button type="submit" class="btn btn-dark px-4 col-s-2" name="login" onclick="return loginValidate();">Log In
                    </button>
                    <button type="submit" class="btn btn-dark px-4 col-s-2" name="cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!----------------START footer------------------------------------------------------------->
<footer class="footer fixed-bottom bg-dark ">
    <a href="mailto:Tarek.K.hallak@gmail.com" class="text-reset">
        <p class="row justify-content-center mt-3 text-white"> &copy; By Tarek Al-Hallak</p>
    </a>
</footer>

</body>
</html>
