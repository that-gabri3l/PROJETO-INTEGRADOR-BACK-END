<?php
require_once __DIR__ . '/sessao.php';

fazerLogout();
header('Location: /index.php');
exit;
