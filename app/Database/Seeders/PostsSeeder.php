<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Database\Seeders;

use \Luminova\Database\Seeder;
use \Luminova\Database\Builder;

class PostsSeeder extends Seeder
{
    /**
     * Post table seeders...
     * 
     * php novakit db:seed --class=PostsSeeder
     * 
     * {@inheritdoc}
     */
    public function run(Builder $builder): void
    {
        $builder->table('posts')->insert([
            [
                'post_uuid' => func()->uuid(),
                'user_id' => 101,
                'post_title' => 'Getting Started with PHP Luminova',
                'post_body' => 'Learn the basics of setting up and running your first project with PHP Luminova framework.',
            ],
            [
                'post_uuid' => func()->uuid(),
                'user_id' => 102,
                'post_title' => 'Advanced Routing Techniques',
                'post_body' => 'Explore the advanced routing features in Luminova, including nested routes and route groups.',
            ],
            [
                'post_uuid' => func()->uuid(),
                'user_id' => 103,
                'post_title' => 'Working with Middleware in Luminova',
                'post_body' => 'Middleware in Luminova allows you to filter and handle HTTP requests efficiently.',
            ],
            [
                'post_uuid' => func()->uuid(),
                'user_id' => 104,
                'post_title' => 'Creating a REST API in Luminova',
                'post_body' => 'Step-by-step guide on creating a RESTful API with authentication and error handling in Luminova.',
            ],
            [
                'post_uuid' => func()->uuid(),
                'user_id' => 105,
                'post_title' => 'Optimizing Luminova for Production',
                'post_body' => 'Best practices for deploying and optimizing Luminova applications for a production environment.',
            ]
        ]);        
    }
}