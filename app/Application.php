<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App;

use \Luminova\Core\CoreApplication;
use \Luminova\Security\JWTAuth;
use \Luminova\Interface\LazyInterface;
use \Luminova\Utils\LazyObject;

class Application extends CoreApplication 
{
    /**
     * Instance of JWT Authentication helper class.
     * 
     * @var JWTAuth|LazyInterface|null $jwt 
     */
    protected JWTAuth|LazyInterface|null $jwt = null; 

    /**
     * {@inheritdoc}
     */
    protected function onCreate(): void 
    {
        $this->jwt = LazyObject::newObject(fn(): JWTAuth => JWTAuth::getInstance(
            algo: 'HS256',
            salt: 'my-password-salt',
            iss: 'https://example.com',
            aud: 'https://example.com/api'
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function onFinish(array $info): void {}

    /**
     * {@inheritdoc}
     */
    protected function onContextInstalled(string $context): void {}

    /**
     * {@inheritdoc}
     */
    protected function onViewPresent(string $uri): void {}

    /**
     * {@inheritdoc}
     */
    protected function onCommandPresent(array $options): void {}
}