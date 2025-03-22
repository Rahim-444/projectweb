<?php
// logout.php - Script de déconnexion
session_start();
session_destroy();
header('Location: index.php');
exit;
?>

