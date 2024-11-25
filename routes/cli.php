<?php
use \Luminova\Routing\Router;
/**
 * This file handles all CLI commands (e.g, `php index.php demo hello'`).
 * 
 * The following global variables are available in the current file:
 * 
 * @var \Luminova\Routing\Router $router The routing instance.
 * @var \App\Application $app The application instance that provides access to the overall application context.
 * @var string $context The name of the current routing context (this file's context).
 */

$router->group('posts', function (Router $router) {
   $router->before('posts', 'PostsCommand::auth');
   
   // CRUD routes for posts
   $router->command('/list', 'PostsCommand::list');         // Retrieve all posts
   $router->command('/get', 'PostsCommand::show');          // Retrieve a specific post by ID
   $router->command('/create', 'PostsCommand::create');     // Create a new post
   $router->command('/update', 'PostsCommand::update');     // Update an existing post
   $router->command('/delete', 'PostsCommand::delete');     // Delete a post by ID
});