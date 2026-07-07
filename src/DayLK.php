<?php

namespace Teknyo\NICValidator;

/**
 * Sri Lankan NIC calendar convention utilities.
 * The Sri Lankan government treats every year as having 366 days when encoding
 * birthdays in NICs. February is always 29 days, regardless of whether the birth
 * year is actually a leap year. This is the official NIC calendar convention.
 */
class DayLK
{
    /** Days in each month according to NIC convention (Feb always 29) */
    public const MONTH_DAYS = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    /** Total days in a year for NIC calculations */
    public const TOTAL_DAYS_IN_YEAR = 366;

    /**
     * Get current date parts in Sri Lanka timezone
     * @return array{year: int, month: int, day: int}
     */
    public static function now(): array
    {
        $timezone = new \DateTimeZone('Asia/Colombo');
        $now = new \DateTime('now', $timezone);

        return [
            'year' => (int)$now->format('Y'),
            'month' => (int)$now->format('n'),
            'day' => (int)$now->format('j'),
        ];
    }

    /**
     * Calculate day of year from month and day
     * @param int $month Month (1-12)
     * @param int $day Day (1-31)
     * @return int Day of year (1-366)
     */
    public static function dayOfYear(int $month, int $day): int
    {
        $total = 0;
        for ($i = 0; $i < $month - 1; $i++) {
            $total += self::MONTH_DAYS[$i];
        }
        return $total + $day;
    }

    /**
     * Get current day of year in Sri Lanka timezone
     * @return int Current day of year (1-366)
     */
    public static function currentDayOfYear(): int
    {
        $now = self::now();
        return self::dayOfYear($now['month'], $now['day']);
    }

    /**
     * Convert day of year back to month and day
     * @param int $dayOfYear Day of year (1-366)
     * @return array{month: int, day: int}
     */
    public static function toDate(int $dayOfYear): array
    {
        $remaining = $dayOfYear;
        for ($month = 0; $month < 12; $month++) {
            if ($remaining <= self::MONTH_DAYS[$month]) {
                return ['month' => $month + 1, 'day' => $remaining];
            }
            $remaining -= self::MONTH_DAYS[$month];
        }
        // Handle edge case for day 366
        return ['month' => 12, 'day' => 31];
    }
}