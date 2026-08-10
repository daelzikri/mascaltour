<?php
/**
 * Admin Root Index Handler - Auto redirect
 */
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
