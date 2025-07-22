<?php

declare(strict_types=1);

namespace Simensen\EphemeralTodos\Tests\Testing;

use Carbon\Carbon;

/**
 * Trait for managing Carbon test time in PHPUnit tests.
 * 
 * This trait provides standardized setUp/tearDown methods for Carbon time management,
 * eliminating the need to duplicate Carbon::setTestNow() calls across test classes.
 * 
 * Usage:
 *   class MyTest extends TestCase
 *   {
 *       use ManagesCarbonTime;
 *       
 *       // Tests will automatically have Carbon time set to default test time
 *   }
 * 
 * Custom test time:
 *   protected function setUp(): void
 *   {
 *       $this->setUpCarbonTime('2025-06-15 14:30:00');
 *   }
 */
trait ManagesCarbonTime
{
    /**
     * Default test timestamp used when no custom time is specified.
     */
    protected string $defaultTestTime = '2025-01-19 12:00:00';

    protected function setUp(): void
    {
        if (method_exists(parent::class, 'setUp')) {
            parent::setUp();
        }

        $this->setUpCarbonTime();
    }

    protected function tearDown(): void
    {
        // Reset Carbon's test time to prevent pollution between tests
        Carbon::setTestNow();

        if (method_exists(parent::class, 'tearDown')) {
            parent::tearDown();
        }
    }

    /**
     * Set up Carbon test time with optional custom timestamp.
     * 
     * @param string|null $testTime Custom test time, uses default if null
     */
    protected function setUpCarbonTime(?string $testTime = null): void
    {
        $time = $testTime ?? $this->defaultTestTime;
        Carbon::setTestNow($time);
    }

    /**
     * Get the default test time used by this trait.
     */
    protected function getDefaultTestTime(): string
    {
        return $this->defaultTestTime;
    }

    /**
     * Override the default test time for this test class.
     */
    protected function setDefaultTestTime(string $testTime): void
    {
        $this->defaultTestTime = $testTime;
    }
}