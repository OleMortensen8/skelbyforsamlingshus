<?php

/**
 * EventManager Fixtures
 *
 * This class provides test data for EventManager tests.
 */
class EventManagerFixtures
{
    /**
     * Get valid XML string for testing
     *
     * @return string Valid XML string with events
     */
    public static function getValidXmlString(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<events>
    <event>
        <title>Summer Festival</title>
        <date>2023-07-15</date>
        <time>14:00</time>
        <location>Skelby Forsamlingshus</location>
        <description>Annual summer festival with music and activities for the whole family.</description>
    </event>
    <event>
        <title>Christmas Party</title>
        <date>2023-12-24</date>
        <time>18:00</time>
        <location>Skelby Forsamlingshus</location>
        <description>Traditional Christmas celebration with dinner and dancing.</description>
    </event>
    <event>
        <title>New Year\'s Eve</title>
        <date>2023-12-31</date>
        <time>20:00</time>
        <location>Skelby Forsamlingshus</location>
        <description>Ring in the new year with friends and neighbors.</description>
    </event>
</events>';
    }

    /**
     * Get empty XML string for testing
     *
     * @return string Empty XML string
     */
    public static function getEmptyXmlString(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<events>
</events>';
    }

    /**
     * Get invalid XML string for testing
     *
     * @return string Invalid XML string
     */
    public static function getInvalidXmlString(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<events>
    <event>
        <title>Broken Event</title>
        <date>2023-07-15</date>
        <time>14:00</time>
        <location>Skelby Forsamlingshus</location>
        <description>This event has invalid XML.
    </event>
</events>';
    }

    /**
     * Get XML string with special characters for testing
     *
     * @return string XML string with special characters
     */
    public static function getXmlWithSpecialChars(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
<events>
    <event>
        <title>Special &amp; Characters</title>
        <date>2023-07-15</date>
        <time>14:00</time>
        <location>Skelby &lt;Forsamlingshus&gt;</location>
        <description>This event has &lt;special&gt; &amp; "characters" to test HTML escaping.</description>
    </event>
</events>';
    }

    /**
     * Get SimpleXML object for testing
     *
     * @return SimpleXMLElement SimpleXML object with events
     */
    public static function getSimpleXmlObject(): \SimpleXMLElement
    {
        return new \SimpleXMLElement(self::getValidXmlString());
    }

    /**
     * Get empty SimpleXML object for testing
     *
     * @return SimpleXMLElement Empty SimpleXML object
     */
    public static function getEmptySimpleXmlObject(): \SimpleXMLElement
    {
        return new \SimpleXMLElement(self::getEmptyXmlString());
    }

    /**
     * Get expected HTML output for valid XML
     *
     * @return string Expected HTML output
     */
    public static function getExpectedHtmlOutput(): string
    {
        return "<div class='arrangement'><h2>Summer Festival</h2><h3>Dato: 2023-07-15</h3><h3>Tid: 14:00</h3><h3> Stedet: Skelby Forsamlingshus</h3><h4>Bekrivelse: Annual summer festival with music and activities for the whole family.</h4></div><hr><div class='arrangement'><h2>Christmas Party</h2><h3>Dato: 2023-12-24</h3><h3>Tid: 18:00</h3><h3> Stedet: Skelby Forsamlingshus</h3><h4>Bekrivelse: Traditional Christmas celebration with dinner and dancing.</h4></div><hr><div class='arrangement'><h2>New Year's Eve</h2><h3>Dato: 2023-12-31</h3><h3>Tid: 20:00</h3><h3> Stedet: Skelby Forsamlingshus</h3><h4>Bekrivelse: Ring in the new year with friends and neighbors.</h4></div><hr>";
    }

    /**
     * Get expected HTML output for empty XML
     *
     * @return string Expected HTML output for empty XML
     */
    public static function getExpectedEmptyOutput(): string
    {
        return "<h2>Arrangementsliste forberedes... </h2>";
    }

    /**
     * Get expected HTML output for XML with special characters
     *
     * @return string Expected HTML output for XML with special characters
     */
    public static function getExpectedSpecialCharsOutput(): string
    {
        return "<div class='arrangement'><h2>Special &amp; Characters</h2><h3>Dato: 2023-07-15</h3><h3>Tid: 14:00</h3><h3> Stedet: Skelby &lt;Forsamlingshus&gt;</h3><h4>Bekrivelse: This event has &lt;special&gt; &amp; \"characters\" to test HTML escaping.</h4></div><hr>";
    }
}