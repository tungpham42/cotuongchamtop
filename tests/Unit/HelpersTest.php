<?php

namespace Tests\Unit;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_contains_cjk_detects_chinese(): void
    {
        $this->assertTrue(containsCJK('中国'));
    }

    public function test_contains_cjk_detects_japanese(): void
    {
        $this->assertTrue(containsCJK('こんにちは'));
    }

    public function test_contains_cjk_detects_korean(): void
    {
        $this->assertTrue(containsCJK('안녕하세요'));
    }

    public function test_contains_cjk_returns_false_for_latin_text(): void
    {
        $this->assertFalse(containsCJK('Hello Xiangqi'));
    }

    public function test_contains_cjk_returns_false_for_empty_string(): void
    {
        $this->assertFalse(containsCJK(''));
    }
}
