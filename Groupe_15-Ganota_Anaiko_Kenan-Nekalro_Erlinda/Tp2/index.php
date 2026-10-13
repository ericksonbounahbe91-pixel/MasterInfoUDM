<?php
require_once 'config.php';
redirect(!empty($_SESSION['user']) ? 'dashboard.php' : 'login.php');
