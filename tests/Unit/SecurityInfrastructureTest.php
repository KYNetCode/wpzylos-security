<?php

defined('ABSPATH') || exit;

declare(strict_types=1);

namespace WPZylos\Framework\Security\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Security\SecurityServiceProvider;
use WPZylos\Framework\Security\Middleware\AuthMiddleware;
use WPZylos\Framework\Security\Middleware\NonceMiddleware;

/**
 * Tests for security infrastructure classes.
 */
class SecurityInfrastructureTest extends TestCase
{
    public function testSecurityServiceProviderIsInstantiable(): void
    {
        $provider = new SecurityServiceProvider();
        $this->assertInstanceOf(SecurityServiceProvider::class, $provider);
    }

    public function testAuthMiddlewareClassExists(): void
    {
        $this->assertTrue(class_exists(AuthMiddleware::class));
    }

    public function testAuthMiddlewareHasHandleMethod(): void
    {
        $this->assertTrue(method_exists(AuthMiddleware::class, 'handle'));
    }

    public function testNonceMiddlewareClassExists(): void
    {
        $this->assertTrue(class_exists(NonceMiddleware::class));
    }

    public function testNonceMiddlewareHasHandleMethod(): void
    {
        $this->assertTrue(method_exists(NonceMiddleware::class, 'handle'));
    }
}
