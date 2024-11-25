<?php
/**
 * Luminova Framework Index front controller.
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
declare(strict_types=1);

use \Luminova\Boot;
// use \Luminova\Routing\Prefix;
// use \App\Controllers\Errors\ViewErrors;

require_once __DIR__ . '/../system/Boot.php';

/**
 * Ensure that we are in front controller while running script in cli mode
 */
if (getcwd() . DIRECTORY_SEPARATOR !== FRONT_CONTROLLER) {
    chdir(FRONT_CONTROLLER);
}

/**
 * FOR ATTRIBUTE ROUTING
 * 
 * Load application route context based on PHP attribute routing.
 */
Boot::http()->router->context()->run();

/**
 * FOR NON ATTRIBUTE ROUTING
 * 
 * Load application route context based on method-based routing.
 * Uncomment this below code if using method-based routing (Non-Attribute).
 * 
 * @see /routes/web.php - Main web-pages routes
 * @see /routes/api.php - APIs routes
 * @see /routes/cli.php - CLI routes
 */
/*
Boot::http()->router->context(
    [
        'prefix' => Prefix::WEB,
        'error'  => [ViewErrors::class, 'onWebError'] 
    ],
    [
        'prefix' => Prefix::API,
        'error'  => [ViewErrors::class, 'onRestError'] 
    ],
    [
        'prefix' => Prefix::CLI
    ]
)->run();*/