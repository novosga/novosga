<?php

/*
 * Default Novo SGA configuration, please don't edit this file. 
 * For custom configuration make a copy named app.php in the same directory
 */

 use App\Configuration\DefaultQueueOrderingHandler;

return [
    'queue' => [
        'ordering' => DefaultQueueOrderingHandler::class,
    ]
];
