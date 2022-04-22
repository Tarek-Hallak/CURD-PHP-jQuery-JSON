<?php // line 1 added to enable color highlight
session_start();

unset($_SESSION['user_name']);
unset($_SESSION['user_id']);
unset($_SESSION['user_email']);
header('Location: index.php');
