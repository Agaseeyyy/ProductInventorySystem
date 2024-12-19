<?php
require_once 'php/auth_functions.php';

// Logout the user
logoutUser();

// Redirect to login page
header("Location: login.php");
exit();