<?php
use \Luminova\Routing\Router;
/**
 * This file handles all URI that start with `api` (e.g, https://example.com/api)
 * 
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Routing\Router $router The routing instance.
 * @var \App\Application $app The application instance that provides access to the overall application context.
 * @var string $context The name of the current routing context (this file's context).
 */

 $router->any('/', 'RestController::index');

 $router->bind('/v1/posts', function(Router $router) {
    $router->middleware(Router::ANY_METHODS, '/(:root)', 'RestController::auth');
    
    // CRUD routes for posts
    $router->get('/', 'RestController::list');                          // Retrieve all posts
    $router->get('/(:int)', 'RestController::read');                    // Retrieve a specific post by ID
    $router->post('/create', 'RestController::create');                 // Create a new post
    $router->put('/update/(:int)/(:int)', 'RestController::update');    // Update an existing post
    $router->delete('/delete/(:int)/(:int)', 'RestController::delete'); // Delete a post by ID
 });