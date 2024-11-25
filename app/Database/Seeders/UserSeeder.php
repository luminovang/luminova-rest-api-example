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

class UserSeeder extends Seeder
{
    /**
     * User table seeders.
     * 
     * php novakit db:seed --class=UserSeeder
     * 
     * {@inheritdoc}
     */
    public function run(Builder $builder): void
    {
        $builder->table('users')->insert([
            ['user_name' => 'Peter', 'user_email' => 'peter@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Alice', 'user_email' => 'alice@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'John', 'user_email' => 'john@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Sarah', 'user_email' => 'sarah@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Michael', 'user_email' => 'michael@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Emily', 'user_email' => 'emily@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'James', 'user_email' => 'james@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Olivia', 'user_email' => 'olivia@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Robert', 'user_email' => 'robert@example.com', 'api_usage_quota' => 0],
            ['user_name' => 'Linda', 'user_email' => 'linda@example.com', 'api_usage_quota' => 0]
        ]);        
    }
}