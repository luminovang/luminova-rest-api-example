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

class Posts extends BaseModel 
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'posts'; 

    /**
     * {@inheritdoc}
     */
    protected string $primaryKey = 'pid'; 

    /**
     *  {@inheritdoc}
     */
    protected bool $cacheable = false;

    public function updatePost(string|int $post_id, string|int $user_id, array $data): bool  
    {
        $this->assertAllowedColumns($this->updatable, $data, 'update');

        return $this->builder->table($this->table)
            ->where($this->primaryKey, '=', $post_id)
            ->and('user_id', '=', $user_id)
            ->update($data) > 0;
    }

    public function deletePost(string|int $post_id, string|int $user_id): bool 
    {
        return $this->builder->table($this->table)
            ->where($this->primaryKey, '=', $post_id)
            ->and('user_id', '=', $user_id)
            ->delete() > 0;
    }
}