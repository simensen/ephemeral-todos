<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Unit\Testing;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Simensen\EphemeralTodos\Testing\TestScenarioBuilder;

class BoundaryConditionTest extends TestCase
{
    public function testCrossesDayBoundaryDetection()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Day Boundary Test')
            ->daily()
            ->at('23:30');

        // Test scenarios that cross day boundary
        $beforeMidnight = Carbon::parse('2025-03-15 23:30:00');
        $afterMidnight = Carbon::parse('2025-03-16 00:30:00');
        
        $this->assertTrue($scenario->crossesDayBoundary($beforeMidnight, $afterMidnight));
        
        // Test scenarios within same day
        $morning = Carbon::parse('2025-03-15 09:00:00');
        $afternoon = Carbon::parse('2025-03-15 15:00:00');
        
        $this->assertFalse($scenario->crossesDayBoundary($morning, $afternoon));
    }

    public function testCrossesDayBoundaryEdgeCases()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Edge Case Test');

        // Test exactly at midnight
        $beforeMidnight = Carbon::parse('2025-03-15 23:59:59');
        $atMidnight = Carbon::parse('2025-03-16 00:00:00');
        
        $this->assertTrue($scenario->crossesDayBoundary($beforeMidnight, $atMidnight));
        
        // Test same exact time (no boundary crossing)
        $sameTime = Carbon::parse('2025-03-15 12:00:00');
        
        $this->assertFalse($scenario->crossesDayBoundary($sameTime, $sameTime));
        
        // Test multiple day boundary crossings
        $dayOne = Carbon::parse('2025-03-15 10:00:00');
        $dayThree = Carbon::parse('2025-03-17 10:00:00');
        
        $this->assertTrue($scenario->crossesDayBoundary($dayOne, $dayThree));
    }

    public function testAroundDSTTransitionDetection()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('DST Transition Test')
            ->inTimezone('America/New_York');

        // Spring forward DST transition 2025 (second Sunday in March)
        // 2025-03-09 02:00:00 -> 03:00:00 (spring forward)
        $beforeSpringDST = Carbon::parse('2025-03-09 01:30:00', 'America/New_York');
        $afterSpringDST = Carbon::parse('2025-03-09 03:30:00', 'America/New_York');
        
        $this->assertTrue($scenario->aroundDSTTransition($beforeSpringDST, $afterSpringDST));
        
        // Fall back DST transition 2025 (first Sunday in November)  
        // 2025-11-02 02:00:00 -> 01:00:00 (fall back)
        $beforeFallDST = Carbon::parse('2025-11-02 01:30:00', 'America/New_York');
        $afterFallDST = Carbon::parse('2025-11-02 02:30:00', 'America/New_York');
        
        $this->assertTrue($scenario->aroundDSTTransition($beforeFallDST, $afterFallDST));
        
        // Regular time without DST transition
        $regularTimeStart = Carbon::parse('2025-06-15 10:00:00', 'America/New_York');
        $regularTimeEnd = Carbon::parse('2025-06-15 14:00:00', 'America/New_York');
        
        $this->assertFalse($scenario->aroundDSTTransition($regularTimeStart, $regularTimeEnd));
    }

    public function testLeapYearBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Leap Year Test');

        // Test leap year February 29th scenarios
        $leapYearFeb28 = Carbon::parse('2024-02-28 23:00:00');
        $leapYearFeb29 = Carbon::parse('2024-02-29 01:00:00');
        $leapYearMar1 = Carbon::parse('2024-03-01 01:00:00');
        
        // Verify leap year date is valid
        $this->assertTrue($scenario->isLeapYear(2024));
        $this->assertTrue($scenario->crossesDayBoundary($leapYearFeb28, $leapYearFeb29));
        $this->assertTrue($scenario->crossesDayBoundary($leapYearFeb29, $leapYearMar1));
        
        // Test non-leap year February scenarios
        $nonLeapYearFeb28 = Carbon::parse('2025-02-28 23:00:00');
        $nonLeapYearMar1 = Carbon::parse('2025-03-01 01:00:00');
        
        $this->assertFalse($scenario->isLeapYear(2025));
        $this->assertTrue($scenario->crossesDayBoundary($nonLeapYearFeb28, $nonLeapYearMar1));
    }

    public function testMonthBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Month Boundary Test');

        // Test end of January to beginning of February
        $endOfJanuary = Carbon::parse('2025-01-31 23:30:00');
        $beginningOfFebruary = Carbon::parse('2025-02-01 00:30:00');
        
        $this->assertTrue($scenario->crossesMonthBoundary($endOfJanuary, $beginningOfFebruary));
        
        // Test within same month
        $midJanuary1 = Carbon::parse('2025-01-15 10:00:00');
        $midJanuary2 = Carbon::parse('2025-01-20 14:00:00');
        
        $this->assertFalse($scenario->crossesMonthBoundary($midJanuary1, $midJanuary2));
        
        // Test crossing year boundary (December to January)
        $endOfYear = Carbon::parse('2024-12-31 23:00:00');
        $beginningOfYear = Carbon::parse('2025-01-01 01:00:00');
        
        $this->assertTrue($scenario->crossesMonthBoundary($endOfYear, $beginningOfYear));
        $this->assertTrue($scenario->crossesYearBoundary($endOfYear, $beginningOfYear));
    }

    public function testQuarterBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Quarter Boundary Test');

        // Test Q1 to Q2 boundary (March to April)
        $endOfQ1 = Carbon::parse('2025-03-31 20:00:00');
        $beginningOfQ2 = Carbon::parse('2025-04-01 08:00:00');
        
        $this->assertTrue($scenario->crossesQuarterBoundary($endOfQ1, $beginningOfQ2));
        
        // Test within same quarter
        $q2Start = Carbon::parse('2025-04-01 10:00:00');
        $q2Middle = Carbon::parse('2025-05-15 14:00:00');
        
        $this->assertFalse($scenario->crossesQuarterBoundary($q2Start, $q2Middle));
        
        // Test all quarter boundaries
        $quarters = [
            ['end' => '2025-03-31', 'start' => '2025-04-01'], // Q1 -> Q2
            ['end' => '2025-06-30', 'start' => '2025-07-01'], // Q2 -> Q3  
            ['end' => '2025-09-30', 'start' => '2025-10-01'], // Q3 -> Q4
            ['end' => '2025-12-31', 'start' => '2026-01-01'], // Q4 -> Q1 (next year)
        ];
        
        foreach ($quarters as $quarter) {
            $endDate = Carbon::parse($quarter['end'] . ' 23:00:00');
            $startDate = Carbon::parse($quarter['start'] . ' 01:00:00');
            
            $this->assertTrue(
                $scenario->crossesQuarterBoundary($endDate, $startDate),
                "Expected quarter boundary crossing from {$quarter['end']} to {$quarter['start']}"
            );
        }
    }

    public function testWeekendBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Weekend Boundary Test');

        // Test Friday evening to Saturday morning (entering weekend)
        $fridayEvening = Carbon::parse('2025-03-14 23:00:00'); // Friday
        $saturdayMorning = Carbon::parse('2025-03-15 08:00:00'); // Saturday
        
        $this->assertTrue($scenario->crossesWeekendBoundary($fridayEvening, $saturdayMorning));
        
        // Test Sunday evening to Monday morning (leaving weekend)
        $sundayEvening = Carbon::parse('2025-03-16 22:00:00'); // Sunday
        $mondayMorning = Carbon::parse('2025-03-17 07:00:00'); // Monday
        
        $this->assertTrue($scenario->crossesWeekendBoundary($sundayEvening, $mondayMorning));
        
        // Test within weekend (Saturday to Sunday)
        $saturdayAfternoon = Carbon::parse('2025-03-15 14:00:00'); // Saturday
        $sundayAfternoon = Carbon::parse('2025-03-16 16:00:00'); // Sunday
        
        $this->assertFalse($scenario->crossesWeekendBoundary($saturdayAfternoon, $sundayAfternoon));
        
        // Test within weekdays (Tuesday to Thursday)
        $tuesday = Carbon::parse('2025-03-18 10:00:00'); // Tuesday
        $thursday = Carbon::parse('2025-03-20 15:00:00'); // Thursday
        
        $this->assertFalse($scenario->crossesWeekendBoundary($tuesday, $thursday));
    }

    public function testBusinessHourBoundaryScenarios()
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('Business Hours Test')
            ->withBusinessHours('09:00', '17:00');

        // Test entering business hours (before 9 AM to after 9 AM)
        $beforeBusiness = Carbon::parse('2025-03-17 08:30:00'); // Monday before 9 AM
        $duringBusiness = Carbon::parse('2025-03-17 09:30:00'); // Monday after 9 AM
        
        $this->assertTrue($scenario->crossesBusinessHourBoundary($beforeBusiness, $duringBusiness));
        
        // Test leaving business hours (before 5 PM to after 5 PM)
        $beforeClose = Carbon::parse('2025-03-17 16:30:00'); // Monday before 5 PM
        $afterClose = Carbon::parse('2025-03-17 17:30:00'); // Monday after 5 PM
        
        $this->assertTrue($scenario->crossesBusinessHourBoundary($beforeClose, $afterClose));
        
        // Test within business hours
        $morningBusiness = Carbon::parse('2025-03-17 10:00:00'); // Monday 10 AM
        $afternoonBusiness = Carbon::parse('2025-03-17 14:00:00'); // Monday 2 PM
        
        $this->assertFalse($scenario->crossesBusinessHourBoundary($morningBusiness, $afternoonBusiness));
        
        // Test weekend (business hours don't apply)
        $saturdayMorning = Carbon::parse('2025-03-15 10:00:00'); // Saturday
        $saturdayAfternoon = Carbon::parse('2025-03-15 14:00:00'); // Saturday
        
        $this->assertFalse($scenario->crossesBusinessHourBoundary($saturdayMorning, $saturdayAfternoon));
    }

    /**
     * @dataProvider dstTransitionProvider
     */
    public function testDSTTransitionEdgeCases(string $timezone, string $beforeTime, string $afterTime, bool $expectedTransition)
    {
        $scenario = TestScenarioBuilder::create()
            ->withName('DST Edge Cases')
            ->inTimezone($timezone);

        $before = Carbon::parse($beforeTime, $timezone);
        $after = Carbon::parse($afterTime, $timezone);
        
        $this->assertEquals(
            $expectedTransition,
            $scenario->aroundDSTTransition($before, $after),
            "DST transition detection failed for {$timezone} from {$beforeTime} to {$afterTime}"
        );
    }

    public static function dstTransitionProvider(): array
    {
        return [
            // US Eastern Time Spring Forward 2025
            ['America/New_York', '2025-03-09 01:30:00', '2025-03-09 03:30:00', true],
            ['America/New_York', '2025-03-09 01:00:00', '2025-03-09 01:30:00', false],
            
            // US Eastern Time Fall Back 2025
            ['America/New_York', '2025-11-02 01:30:00', '2025-11-02 02:30:00', true],
            ['America/New_York', '2025-11-02 00:30:00', '2025-11-02 01:30:00', false],
            
            // European Central Time Spring Forward 2025
            ['Europe/Berlin', '2025-03-30 01:30:00', '2025-03-30 03:30:00', true],
            ['Europe/Berlin', '2025-03-30 01:00:00', '2025-03-30 01:30:00', false],
            
            // Non-DST timezone (no transitions)
            ['UTC', '2025-03-09 01:30:00', '2025-03-09 03:30:00', false],
            ['Asia/Tokyo', '2025-03-09 01:30:00', '2025-03-09 03:30:00', false],
        ];
    }
}