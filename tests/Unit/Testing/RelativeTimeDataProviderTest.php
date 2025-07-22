<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Simensen\EphemeralTodos\AfterDueBy;
use Simensen\EphemeralTodos\AfterExistingFor;
use Simensen\EphemeralTodos\Tests\Testing\RelativeTimeDataProvider;

class RelativeTimeDataProviderTest extends TestCase
{
    use RelativeTimeDataProvider;

    /**
     * @dataProvider relativeTimeMethodsProvider
     */
    public function testRelativeTimeMethodsWithAfterDueBy(string $methodName, int $expectedSeconds)
    {
        $method = [AfterDueBy::class, $methodName];
        $this->assertTrue(is_callable($method), "Method {$methodName} should be callable on AfterDueBy");
        
        $instance = call_user_func($method);
        $this->assertEquals($expectedSeconds, $instance->timeInSeconds());
    }

    /**
     * @dataProvider relativeTimeMethodsProvider
     */
    public function testRelativeTimeMethodsWithAfterExistingFor(string $methodName, int $expectedSeconds)
    {
        $method = [AfterExistingFor::class, $methodName];
        $this->assertTrue(is_callable($method), "Method {$methodName} should be callable on AfterExistingFor");
        
        $instance = call_user_func($method);
        $this->assertEquals($expectedSeconds, $instance->timeInSeconds());
    }

    public function testDataProviderReturnsCorrectStructure()
    {
        $data = $this->relativeTimeMethodsProvider();
        
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        
        foreach ($data as $methodName => $testCase) {
            $this->assertIsString($methodName);
            $this->assertIsArray($testCase);
            $this->assertCount(2, $testCase);
            $this->assertIsString($testCase[0]); // method name
            $this->assertIsInt($testCase[1]);    // expected seconds
        }
    }

    public function testDataProviderIncludesAllExpectedMethods()
    {
        $data = $this->relativeTimeMethodsProvider();
        $methodNames = array_column($data, 0);
        
        $expectedMethods = [
            'oneMinute', 'twoMinutes', 'fiveMinutes', 'tenMinutes', 'fifteenMinutes',
            'twentyMinutes', 'thirtyMinutes', 'fortyFiveMinutes',
            'oneHour', 'sixtyMinutes', 'ninetyMinutes', 'twoHours', 'threeHours',
            'fourHours', 'sixHours', 'twelveHours',
            'oneDay', 'twoDays', 'threeDays',
            'oneWeek', 'sevenDays', 'twoWeeks'
        ];
        
        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains($expectedMethod, $methodNames, "Method {$expectedMethod} should be in data provider");
        }
    }

    public function testDataProviderValuesMatchExpectedSeconds()
    {
        $data = $this->relativeTimeMethodsProvider();
        
        // Test a few key values to ensure correctness
        $expectations = [
            'oneMinute' => 60,
            'oneHour' => 3600,
            'sixtyMinutes' => 3600, // Should be same as oneHour
            'oneDay' => 86400,
            'oneWeek' => 604800,
            'sevenDays' => 604800, // Should be same as oneWeek
        ];
        
        foreach ($expectations as $method => $expectedSeconds) {
            $found = false;
            foreach ($data as $testCase) {
                if ($testCase[0] === $method) {
                    $this->assertEquals($expectedSeconds, $testCase[1], "Method {$method} should return {$expectedSeconds} seconds");
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Method {$method} should be found in data provider");
        }
    }
}