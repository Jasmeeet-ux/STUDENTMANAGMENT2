<?php
session_start();
session_unset();
session_destroy();
header("Location: sub_admin_login.php");
exit;
?>