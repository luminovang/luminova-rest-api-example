<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Database\Migrations;

use \Luminova\Database\Migration;
use \Luminova\Database\Table;
use \Luminova\Database\Schema;

class PostsMigration extends Migration
{
    /**
     * Post database table blueprint.
     * 
     * php novakit db:migrate --class=PostsMigration
     * 
     * {@inheritdoc}
     */
    public function up(): void
    {
        Schema::create('posts', function (Table $table) {
            $table->prettify = true;
            $table->database = Table::SQLITE;
            
            $table->id('pid');
            $table->uuid('post_uuid')->nullable(false)->unique();
            $table->integer('user_id', 5)->nullable(false);
            $table->string('post_title')->nullable(false);
            $table->string('post_image')->nullable();
            $table->text('post_body')->nullable(false);
            $table->timestamps();

            return $table;
        });
    }


    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }

    /**
     * {@inheritdoc}
     */
    public function alter(): void {}
}
