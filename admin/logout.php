<?php
require_once "includes/session_manager.php";
session_unset();
session_destroy();
header("Location: index.php");
exit;