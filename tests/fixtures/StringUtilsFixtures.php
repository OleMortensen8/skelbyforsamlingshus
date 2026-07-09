<?php

/**
 * StringUtils Fixtures
 *
 * This class provides test data for StringUtils tests.
 */
class StringUtilsFixtures
{
    /**
     * Get strings for truncate testing
     *
     * @return array Array of strings for truncate testing
     */
    public static function getTruncateTestStrings(): array
    {
        return [
            'short' => 'This is a short string.',
            'medium' => 'This is a medium length string that might need to be truncated depending on the length parameter.',
            'long' => 'This is a very long string that will definitely need to be truncated. It contains multiple sentences and goes on for quite a while to ensure that it exceeds most reasonable length limits that might be set for truncation. The purpose of this string is to test the truncate method with a string that is guaranteed to be longer than the specified length parameter.',
            'with_special_chars' => 'This string contains special characters like åäö and emoji 😀 to test UTF-8 handling.',
            'empty' => ''
        ];
    }

    /**
     * Get expected truncated strings
     *
     * @return array Array of expected truncated strings
     */
    public static function getExpectedTruncatedStrings(): array
    {
        return [
            // [original_key, length, expected_result]
            ['short', 100, 'This is a short string.'],
            ['medium', 20, 'This is a medium len...'],
            ['medium', 30, 'This is a medium length string...'],
            ['long', 50, 'This is a very long string that will definitely ne...'],
            ['with_special_chars', 25, 'This string contains sp...'],
            ['empty', 10, '']
        ];
    }

    /**
     * Get strings for title case testing
     *
     * @return array Array of strings for title case testing
     */
    public static function getTitleCaseTestStrings(): array
    {
        return [
            'lowercase' => 'this is all lowercase',
            'uppercase' => 'THIS IS ALL UPPERCASE',
            'mixed_case' => 'ThIs Is MiXeD cAsE',
            'with_numbers' => 'string with 123 numbers',
            'with_special_chars' => 'string with åäö and emoji 😀',
            'empty' => ''
        ];
    }

    /**
     * Get expected title case strings
     *
     * @return array Array of expected title case strings
     */
    public static function getExpectedTitleCaseStrings(): array
    {
        return [
            // [original_key, expected_result]
            ['lowercase', 'This Is All Lowercase'],
            ['uppercase', 'This Is All Uppercase'],
            ['mixed_case', 'This Is Mixed Case'],
            ['with_numbers', 'String With 123 Numbers'],
            ['with_special_chars', 'String With Åäö And Emoji 😀'],
            ['empty', '']
        ];
    }

    /**
     * Get custom ellipsis test cases
     *
     * @return array Array of custom ellipsis test cases
     */
    public static function getCustomEllipsisTestCases(): array
    {
        return [
            // [string, length, ellipsis, expected_result]
            ['This is a test string', 10, '...', 'This is a ...'],
            ['This is a test string', 10, ' (more)', 'This is a  (more)'],
            ['This is a test string', 10, '', 'This is a '],
            ['This is a test string', 10, '---', 'This is a ---']
        ];
    }

    /**
     * Get edge case test strings
     *
     * @return array Array of edge case test strings
     */
    public static function getEdgeCaseTestStrings(): array
    {
        return [
            // String exactly at the length limit
            'exact_length' => [
                'string' => 'Exactly 20 characters',
                'length' => 20,
                'expected_truncated' => 'Exactly 20 characters',
                'expected_title_case' => 'Exactly 20 Characters'
            ],
            // String one character over the length limit
            'one_over' => [
                'string' => 'This is 21 characters',
                'length' => 20,
                'expected_truncated' => 'This is 21 characte...',
                'expected_title_case' => 'This Is 21 Characters'
            ],
            // String with only one word
            'one_word' => [
                'string' => 'Supercalifragilisticexpialidocious',
                'length' => 15,
                'expected_truncated' => 'Supercalifragi...',
                'expected_title_case' => 'Supercalifragilisticexpialidocious'
            ],
            // String with multiple spaces
            'multiple_spaces' => [
                'string' => 'This   has   multiple   spaces',
                'length' => 15,
                'expected_truncated' => 'This   has   m...',
                'expected_title_case' => 'This   Has   Multiple   Spaces'
            ]
        ];
    }
}