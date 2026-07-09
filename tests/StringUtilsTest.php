<?php

use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public function testTruncateWithShortString()
    {
        $string = "This is a short string";
        $result = StringUtils::truncate($string, 100);
        $this->assertEquals($string, $result);
    }

    public function testTruncateWithLongString()
    {
        $string = "This is a very long string that needs to be truncated because it exceeds the maximum length";
        $result = StringUtils::truncate($string, 20);
        $this->assertEquals("This is a very long...", $result);
    }

    public function testTruncateWithCustomEllipsis()
    {
        $string = "This is a string that will be truncated with a custom ellipsis";
        $result = StringUtils::truncate($string, 20, ' [more]');
        $this->assertEquals("This is a string that [more]", $result);
    }

    public function testTruncateWithExactLength()
    {
        $string = "Exactly 20 characters";
        $result = StringUtils::truncate($string, 20);
        $this->assertEquals($string, $result);
    }

    public function testTruncateWithMultibyteCharacters()
    {
        $string = "こんにちは世界"; // "Hello World" in Japanese
        $result = StringUtils::truncate($string, 3);
        $this->assertEquals("こんに...", $result);
    }

    public function testToTitleCase()
    {
        $string = "hello world";
        $result = StringUtils::toTitleCase($string);
        $this->assertEquals("Hello World", $result);
    }

    public function testToTitleCaseWithMixedCase()
    {
        $string = "hElLo WoRlD";
        $result = StringUtils::toTitleCase($string);
        $this->assertEquals("Hello World", $result);
    }

    public function testToTitleCaseWithMultibyteCharacters()
    {
        $string = "こんにちは世界"; // "Hello World" in Japanese
        $result = StringUtils::toTitleCase($string);
        // mb_convert_case with MB_CASE_TITLE doesn't change Japanese characters
        $this->assertEquals("こんにちは世界", $result);
    }
}