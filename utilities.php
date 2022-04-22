<?php

function Msg()
{
    if (isset($_SESSION['success'])) {
        $msg = $_SESSION['success'];
        echo "<div class=\"alert alert-success alert-dismissible fade show mb-0\" role=\"alert\">
                   <strong>$msg</strong>
               </div>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $msg = $_SESSION['error'];
        echo "<div class=\"alert alert-danger alert-dismissible fade show mb-0\" role=\"alert\">
                   <strong>$msg</strong>
               </div>";
        unset($_SESSION['error']);
    }
}

//Check if user is logged in
function is_Logged()
{
    if (!isset($_SESSION['user_name'])) {
        die("ACCESS DENIED");
    }
}

//Searching for school if it is already in institution table
//Get institution ID to link it to its profile
function checkSchool($pdo , $i){
    $check = $pdo->prepare("SELECT * from institution where name = :name");
    $check->execute(array(':name' => $_POST['school-' . $i]));
    $checkData = $check->fetch(PDO::FETCH_ASSOC);

    //inserting school into institution table
    if ($checkData === false) {
        $insSQL = "INSERT INTO institution (name) VALUES (:name)";
        $insSTMT = $pdo->prepare($insSQL);
        $insSTMT->execute(array(
            ':name' => $_POST['school-' . $i]
        ));
        $insID = $pdo->lastInsertId();
    } else {
        $insID = $checkData['institution_id'];
    }
    return $insID;
}

