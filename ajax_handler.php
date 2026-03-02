<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$controller = makeAjaxController();
$controller->handle($action, $_GET, $_POST);