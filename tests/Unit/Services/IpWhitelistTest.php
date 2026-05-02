<?php

namespace Tests\Unit\Services;

use App\Http\Middleware\IpWhitelist;
use PHPUnit\Framework\TestCase;

class IpWhitelistTest extends TestCase
{
    private IpWhitelist $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new IpWhitelist();
    }

    private function ipMatches(string $clientIp, string $entry): bool
    {
        $reflection = new \ReflectionClass($this->middleware);
        $method     = $reflection->getMethod('ipMatches');
        $method->setAccessible(true);
        return $method->invoke($this->middleware, $clientIp, $entry);
    }

    /** @test */
    public function exact_ipv4_match_passes()
    {
        $this->assertTrue($this->ipMatches('203.0.113.1', '203.0.113.1'));
    }

    /** @test */
    public function different_exact_ip_fails()
    {
        $this->assertFalse($this->ipMatches('203.0.113.2', '203.0.113.1'));
    }

    /** @test */
    public function cidr_slash_24_matches_within_subnet()
    {
        $this->assertTrue($this->ipMatches('192.168.1.50', '192.168.1.0/24'));
        $this->assertTrue($this->ipMatches('192.168.1.1',  '192.168.1.0/24'));
        $this->assertTrue($this->ipMatches('192.168.1.254','192.168.1.0/24'));
    }

    /** @test */
    public function cidr_slash_24_rejects_outside_subnet()
    {
        $this->assertFalse($this->ipMatches('192.168.2.1', '192.168.1.0/24'));
    }

    /** @test */
    public function cidr_slash_16_matches_within_subnet()
    {
        $this->assertTrue($this->ipMatches('10.0.1.5', '10.0.0.0/16'));
        $this->assertFalse($this->ipMatches('10.1.1.5', '10.0.0.0/16'));
    }

    /** @test */
    public function cidr_slash_32_is_effectively_exact_match()
    {
        $this->assertTrue($this->ipMatches('203.0.113.5', '203.0.113.5/32'));
        $this->assertFalse($this->ipMatches('203.0.113.6', '203.0.113.5/32'));
    }

    /** @test */
    public function localhost_matches_exactly()
    {
        $this->assertTrue($this->ipMatches('127.0.0.1', '127.0.0.1'));
        $this->assertFalse($this->ipMatches('127.0.0.2', '127.0.0.1'));
    }

    /** @test */
    public function cidr_slash_8_matches_entire_class_a_range()
    {
        $this->assertTrue($this->ipMatches('10.255.255.255', '10.0.0.0/8'));
        $this->assertFalse($this->ipMatches('11.0.0.1',      '10.0.0.0/8'));
    }
}
