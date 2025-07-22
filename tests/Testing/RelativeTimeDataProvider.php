<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Testing;

/**
 * Trait providing data provider for relative time convenience method testing.
 * 
 * This trait eliminates duplication in testing relative time methods across
 * AfterDueBy, AfterExistingFor, and other classes with time convenience methods.
 * 
 * Usage:
 *   class MyTest extends TestCase
 *   {
 *       use RelativeTimeDataProvider;
 *       
 *       /**
 *        * @dataProvider relativeTimeMethodsProvider
 *        * /
 *       public function testRelativeTimeMethods(string $methodName, int $expectedSeconds)
 *       {
 *           $instance = MyTimeClass::$methodName();
 *           $this->assertEquals($expectedSeconds, $instance->timeInSeconds());
 *       }
 *   }
 */
trait RelativeTimeDataProvider
{
    /**
     * Data provider for relative time convenience methods.
     * 
     * Returns array mapping method names to their expected seconds values.
     * Each test case contains [methodName, expectedSeconds].
     */
    public static function relativeTimeMethodsProvider(): array
    {
        return [
            'oneMinute' => ['oneMinute', 60],
            'twoMinutes' => ['twoMinutes', 120],
            'fiveMinutes' => ['fiveMinutes', 300],
            'tenMinutes' => ['tenMinutes', 600],
            'fifteenMinutes' => ['fifteenMinutes', 900],
            'twentyMinutes' => ['twentyMinutes', 1200],
            'thirtyMinutes' => ['thirtyMinutes', 1800],
            'fortyFiveMinutes' => ['fortyFiveMinutes', 2700],
            
            'oneHour' => ['oneHour', 3600],
            'sixtyMinutes' => ['sixtyMinutes', 3600],
            'ninetyMinutes' => ['ninetyMinutes', 5400],
            'twoHours' => ['twoHours', 7200],
            'threeHours' => ['threeHours', 10800],
            'fourHours' => ['fourHours', 14400],
            'sixHours' => ['sixHours', 21600],
            'twelveHours' => ['twelveHours', 43200],
            
            'oneDay' => ['oneDay', 86400],
            'twoDays' => ['twoDays', 172800],
            'threeDays' => ['threeDays', 259200],
            
            'oneWeek' => ['oneWeek', 604800],
            'sevenDays' => ['sevenDays', 604800],
            'twoWeeks' => ['twoWeeks', 1209600],
        ];
    }

    /**
     * Data provider for minute-based convenience methods only.
     */
    public static function minuteMethodsProvider(): array
    {
        return [
            'oneMinute' => ['oneMinute', 60],
            'twoMinutes' => ['twoMinutes', 120],
            'fiveMinutes' => ['fiveMinutes', 300],
            'tenMinutes' => ['tenMinutes', 600],
            'fifteenMinutes' => ['fifteenMinutes', 900],
            'twentyMinutes' => ['twentyMinutes', 1200],
            'thirtyMinutes' => ['thirtyMinutes', 1800],
            'fortyFiveMinutes' => ['fortyFiveMinutes', 2700],
        ];
    }

    /**
     * Data provider for hour-based convenience methods only.
     */
    public static function hourMethodsProvider(): array
    {
        return [
            'oneHour' => ['oneHour', 3600],
            'sixtyMinutes' => ['sixtyMinutes', 3600],
            'ninetyMinutes' => ['ninetyMinutes', 5400],
            'twoHours' => ['twoHours', 7200],
            'threeHours' => ['threeHours', 10800],
            'fourHours' => ['fourHours', 14400],
            'sixHours' => ['sixHours', 21600],
            'twelveHours' => ['twelveHours', 43200],
        ];
    }

    /**
     * Data provider for day-based convenience methods only.
     */
    public static function dayMethodsProvider(): array
    {
        return [
            'oneDay' => ['oneDay', 86400],
            'twoDays' => ['twoDays', 172800],
            'threeDays' => ['threeDays', 259200],
        ];
    }

    /**
     * Data provider for week-based convenience methods only.
     */
    public static function weekMethodsProvider(): array
    {
        return [
            'oneWeek' => ['oneWeek', 604800],
            'sevenDays' => ['sevenDays', 604800],
            'twoWeeks' => ['twoWeeks', 1209600],
        ];
    }
}