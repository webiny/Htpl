<?php
/**
 * Webiny Htpl (https://github.com/Webiny/Htpl/)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Htpl\UnitTests\Modifiers;

use Webiny\Htpl\Modifiers\CorePack;

class CorePackTest extends \PHPUnit_Framework_TestCase
{
    public function testAbs()
    {
        $num = -5;
        $this->assertSame(5, CorePack::abs($num));
    }

    public function testCapitalize()
    {
        $str = 'lower string';
        $this->assertSame('Lower String', CorePack::capitalize($str));
    }

    public function testLower()
    {
        $str = 'UPPER STRING';
        $this->assertSame('upper string', CorePack::lower($str));
    }

    public function testUpper()
    {
        $str = 'lower string';
        $this->assertSame('LOWER STRING', CorePack::upper($str));
    }

    public function testFirstUpper()
    {
        $str = 'lower string';
        $this->assertSame('Lower string', CorePack::firstUpper($str));
    }

    public function testDate()
    {
        $ts = time();
        $date = date('Y-m-d H:i:s', $ts);
        $this->assertSame($date, CorePack::date($ts));

        $date = date('d.m.Y H:i:s');
        $this->assertSame($date, CorePack::date($ts, 'd.m.Y H:i:s'));
    }

    public function testTimeAgo()
    {
        $ts = time() - 5;
        $this->assertSame('5 seconds ago', CorePack::timeAgo($ts));

        $ts = time() - 120;
        $this->assertSame('2 minutes ago', CorePack::timeAgo($ts));

        $ts = time() - 7200;
        $this->assertSame('2 hours ago', CorePack::timeAgo($ts));

        $ts = time() - 86400;
        $this->assertSame('1 day ago', CorePack::timeAgo($ts));

        $ts = time() - (86400 * 30 * 3);
        $this->assertSame('3 months ago', CorePack::timeAgo($ts));

        $ts = time() - (86400 * 366);
        $this->assertSame('1 year ago', CorePack::timeAgo($ts));
    }

    public function testDefaultValue()
    {
        $val = 'some val';
        $this->assertSame($val, CorePack::defaultValue(null, $val));
    }

    public function testFormat()
    {
        $str = 'my name is: %s';
        $this->assertSame('my name is: John Snow', CorePack::format($str, ['John Snow']));
    }

    public function testFirst()
    {
        $arr = ['one', 'two'];
        $this->assertSame('one', CorePack::first($arr));
    }

    public function testLast()
    {
        $arr = ['one', 'two'];
        $this->assertSame('two', CorePack::last($arr));
    }

    public function testJoin()
    {
        $arr = ['one', 'two'];
        $this->assertSame('one|two', CorePack::join($arr, '|'));
    }

    public function testKeys()
    {
        $arr = ['a' => 'one', 'b' => 'two'];
        $this->assertSame(['a', 'b'], CorePack::keys($arr));
    }

    public function testValues()
    {
        $arr = ['a' => 'one', 'b' => 'two'];
        $this->assertSame(['one', 'two'], CorePack::values($arr));
    }

    public function testLength()
    {
        $arr = ['a' => 'one', 'b' => 'two'];
        $this->assertSame(2, CorePack::length($arr));

        $str = 'string';
        $this->assertSame(6, CorePack::length($str));
    }

    public function testJsonEncode()
    {
        $arr = ['a' => 'one', 'b' => 'two'];
        $this->assertSame('{"a":"one","b":"two"}', CorePack::jsonEncode($arr));
    }

    public function testNl2Br()
    {
        $str = "Going
Home";
        $this->assertSame('Going<br />
Home', CorePack::nl2br($str));
    }

    public function testRound()
    {
        $num = 3.5;
        $this->assertSame(4.0, CorePack::round($num, 0));

        $num = 3.5;
        $this->assertSame(3.00, CorePack::round($num, 0, 'down'));
    }

    public function testNumberFormat()
    {
        $num = 3500.1;
        $this->assertSame('3,500.10', CorePack::numberFormat($num, 2));
        $this->assertSame('3.500,100', CorePack::numberFormat($num, 3, ',', '.'));
    }

    public function testRaw()
    {
        $str = htmlspecialchars('<br/>', ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
        $this->assertNotEquals('<br/>', $str);
        $this->assertSame('<br/>', CorePack::raw($str));
    }

    public function testReplace()
    {
        $str = 'John loves Kalisi';
        $replacements = ['Kalisi' => 'Tyrion'];
        $this->assertSame('John loves Tyrion', CorePack::replace($str, $replacements));
    }

    public function testStripTags()
    {
        $string = 'Some <div>HTML</div> string';
        $this->assertSame('Some HTML string', CorePack::stripTags($string));

        $string = 'Some <div>HTML</div> string';
        $this->assertSame($string, CorePack::stripTags($string, '<div>'));
    }

    public function testTrim()
    {
        $string = ' some string ';
        $this->assertSame('some string', CorePack::trim($string));

        $string = ' some string ';
        $this->assertSame(' some string', CorePack::trim($string, 'right'));

        $string = ' some string ';
        $this->assertSame('some string ', CorePack::trim($string, 'left'));
    }
}