<?php

declare(strict_types=1);

namespace Chiron\Core\Tests;

use Chiron\Core\Environment;
use PHPUnit\Framework\TestCase;

// TODO : finir d'ajouter tous les tests pour cette classe d'Environment !!!!
class EnvironmentTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['foo']);

        unset($_ENV['bool_1']);
        unset($_ENV['bool_2']);
        unset($_ENV['bool_3']);
        unset($_ENV['bool_4']);
        unset($_ENV['bool_5']);
        unset($_ENV['bool_6']);

        unset($_ENV['empty_1']);
        unset($_ENV['null_1']);

        unset($_ENV['quote_1']);
        unset($_ENV['quote_2']);
        unset($_ENV['quote_3']);
        unset($_ENV['quote_4']);
        unset($_ENV['quote_5']);
        unset($_ENV['quote_6']);
        unset($_ENV['quote_7']);
        unset($_ENV['quote_8']);
        unset($_ENV['quote_9']);
        unset($_ENV['quote_10']);
        unset($_ENV['quote_11']);
        unset($_ENV['quote_12']);
        unset($_ENV['quote_13']);
        unset($_ENV['quote_14']);

        unset($_ENV['numeric_1']);
        unset($_ENV['numeric_2']);

        unset($_ENV['b64_1']);
    }

    public function testEnvironmentGetAndGetDefault(): void
    {
        $_ENV['foo'] = 'bar';

        $env = new Environment();

        self::assertTrue($env->has('foo'));
        self::assertEquals('bar', $env->get('foo'));

        self::assertFalse($env->has('non_existing'));
        self::assertSame('default', $env->get('non_existing', 'default'));
    }

    /**
     * @dataProvider dataObjectProvider
     */
    public function testGetNormalizeValue(string $key, string $value, $expected): void
    {
        $_ENV[$key] = $value;

        $env = new Environment();

        self::assertSame($expected, $env->get($key));
    }

    /**
     * @return array<array<string, string, mixed>>
     */
    public function dataObjectProvider(): array
    {
        return [
            ['bool_1', 'true', true],
            ['bool_2', 'yes', true],
            ['bool_3', 'on', true],
            ['bool_4', 'false', false],
            ['bool_5', 'no', false],
            ['bool_6', 'off', false],
            ['empty_1', 'empty', ''],
            ['null_1', 'null', null],
            ['numeric_1', '123', 123],
            ['numeric_2', '1.23', 1.23],
            ['b64_1', 'base64:dGVzdA==', 'test'],
            ['quote_1', '"true"', 'true'],
            ['quote_2', "'true'", 'true'],
            ['quote_3', '"false"', 'false'],
            ['quote_4', "'false'", 'false'],
            ['quote_5', '"empty"', 'empty'],
            ['quote_6', "'empty'", 'empty'],
            ['quote_7', '"null"', 'null'],
            ['quote_8', "'null'", 'null'],
            ['quote_9', '"123"', '123'],
            ['quote_10', "'123'", '123'],
            ['quote_11', '"1.23"', '1.23'],
            ['quote_12', "'1.23'", '1.23'],
            ['quote_13', '"base64:dGVzdA=="', 'base64:dGVzdA=='],
            ['quote_14', "'base64:dGVzdA=='", 'base64:dGVzdA=='],
        ];
    }
}
