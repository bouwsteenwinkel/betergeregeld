<?php

namespace Tests\Unit\AccessGuard\Radar;

use App\Services\AccessGuard\Radar\Http\SsrfGuard;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * No DB, no HTTP — pure CIDR / hostname unit tests. Runs as a plain
 * PHPUnit test (not Laravel TestCase) so it works regardless of DB
 * availability.
 */
class SsrfGuardTest extends TestCase
{
	private SsrfGuard $guard;

	protected function setUp(): void
	{
		parent::setUp();
		$this->guard = new SsrfGuard();
	}

	#[DataProvider('blockedIpv4')]
	public function test_blocks_private_ipv4_literals(string $ip): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve($ip);
	}

	/** @return array<string, array{string}> */
	public static function blockedIpv4(): array
	{
		return [
			'loopback'         => ['127.0.0.1'],
			'rfc1918 10/8'     => ['10.20.30.40'],
			'rfc1918 172.16/12'=> ['172.16.5.5'],
			'rfc1918 192.168'  => ['192.168.1.1'],
			'link-local'       => ['169.254.169.254'], // AWS metadata
			'cgnat'            => ['100.64.0.1'],
			'zero net'         => ['0.0.0.1'],
			'multicast'        => ['239.0.0.1'],
		];
	}

	#[DataProvider('blockedIpv6')]
	public function test_blocks_private_ipv6_literals(string $ip): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve($ip);
	}

	/** @return array<string, array{string}> */
	public static function blockedIpv6(): array
	{
		return [
			'loopback'    => ['::1'],
			'unique-local'=> ['fc00::1'],
			'link-local'  => ['fe80::1'],
			'unspecified' => ['::'],
		];
	}

	public function test_allows_public_ipv4_literal(): void
	{
		$this->assertSame('1.1.1.1', $this->guard->resolve('1.1.1.1'));
	}

	public function test_allows_public_ipv6_literal(): void
	{
		$this->assertSame('2606:4700:4700::1111', $this->guard->resolve('2606:4700:4700::1111'));
	}

	public function test_rejects_empty_host(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve('   ');
	}

	public function test_rejects_internal_tld_suffix(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve('printer.local');
	}

	public function test_rejects_lan_suffix(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve('nas.lan');
	}

	public function test_rejects_corp_suffix(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve('vault.corp');
	}

	public function test_rejects_host_with_whitespace(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->guard->resolve("evil.com\nrebind.com");
	}
}
