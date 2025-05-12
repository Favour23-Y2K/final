<?php
session_start();

// $_SESSION['safe'] = session_destroy();
// $_SESSION['safe'] = session_unset();
// $_SESSION['safe'] = NULL;
// unset($_SESSION['safe']);

$_SESSION = NULL;
session_destroy();
session_unset();
unset($_SESSION);

header('Location: ../');