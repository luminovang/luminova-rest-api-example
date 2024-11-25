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

class UserMigration extends Migration
{
    /**
     * Users database table blueprint.
     * 
     * php novakit db:migrate --class=UserMigration
     * 
     * {@inheritdoc}
     */
    public function up(): void
    {
        Schema::create('users', function (Table $table) {
            $table->prettify = true;
            $table->database = Table::SQLITE;
            
            $table->id('user_id');
            $table->integer('api_usage_quota', 0, 99999)->default(0);
            $table->string('user_name')->nullable(false);
            $table->string('user_email')->nullable(false);
            $table->timestamps();

            return $table;
        });
    }


    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }

    /**
     * {@inheritdoc}
     */
    public function alter(): void {}
}
