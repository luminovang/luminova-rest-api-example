<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Models;

use \Luminova\Base\BaseModel;

class Users extends BaseModel 
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'users'; 

    /**
     * {@inheritdoc}
     */
    protected string $primaryKey = 'user_id'; 

    /**
     *  {@inheritdoc}
     */
    protected bool $cacheable = false;
}