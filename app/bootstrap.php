<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Support/JsonResponse.php';
require_once __DIR__ . '/Services/TradingService.php';
require_once __DIR__ . '/Http/AjaxController.php';

function makeAjaxController(): AjaxController
{
    return new AjaxController(new TradingService(createPdoConnection()));
}
