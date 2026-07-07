<?php

namespace Teknyo\NICValidator;

/**
 * Sri Lanka NIC Validator and Details Extractor
 *
 * A comprehensive package for validating Sri Lankan National Identity Card (NIC) numbers
 * and extracting birth date and gender information.
 *
 * Features:
 * - Supports both old format (9 digits + V/X) and new format (12 digits)
 * - PHP 8.4+ with full type definitions
 * - Proper date validation using official NIC calendar convention
 */
class NICValidator
{
    /** Old format: 9 digits followed by V or X (case insensitive) */
    private const OLD_NIC_REGEX = '/^\d{9}[VXvx]$/';

    /** New format: 12 digits only */
    private const NEW_NIC_REGEX = '/^\d{12}$/';

    /**
     * Validate NIC format using regex
     */
    private static function validateFormat(string $nic): ?NICFormat
    {
        if (preg_match(self::OLD_NIC_REGEX, $nic)) {
            return NICFormat::OLD;
        }
        if (preg_match(self::NEW_NIC_REGEX, $nic)) {
            return NICFormat::NEW;
        }
        return null;
    }

    /**
     * Convert old NIC to new format (YYYYDDDNNNN)
     * @param string $nic Old format NIC (YYDDDNNNNV/X)
     * @return string|null New format NIC or null if invalid
     */
    public static function convertOldToNewNIC(string $nic): ?string
    {
        $trimmedNIC = trim($nic);

        if (!preg_match(self::OLD_NIC_REGEX, $trimmedNIC)) {
            return null;
        }

        $yearDigits = (int)substr($trimmedNIC, 0, 2);
        // Old format is typically 19xx
        $birthYear = 1900 + $yearDigits;
        $dayOfYear = (int)substr($trimmedNIC, 2, 3);
        $serialPart = substr($trimmedNIC, 5, 4);

        // Format day of year as 3 digits
        $dayStr = str_pad((string)$dayOfYear, 3, '0', STR_PAD_LEFT);

        return "{$birthYear}{$dayStr}{$serialPart}";
    }

    /**
     * Get gender from NIC
     * @param string $nic NIC number
     * @return Gender 'male', 'female', or null if invalid
     */
    public static function getNICGender(string $nic): Gender|null
    {
        $result = self::validateNIC($nic);
        return $result->gender;
    }

    /**
     * Get birth year from NIC
     * @param string $nic NIC number
     * @return int|null Birth year (4 digits) or null if invalid
     */
    public static function getNICBirthYear(string $nic): ?int
    {
        $result = self::validateNIC($nic);
        return $result->birthYear;
    }

    /**
     * Get day of year from NIC
     * @param string $nic NIC number
     * @return int|null Day of year (1-366) or null if invalid
     */
    public static function getNICDayOfYear(string $nic): ?int
    {
        $trimmedNIC = trim($nic);
        $format = self::validateFormat($trimmedNIC);

        if (!$format) {
            return null;
        }

        if ($format === NICFormat::OLD) {
            $dayOfYear = (int)substr($trimmedNIC, 2, 3);
            // For old format, if day > 500, subtract 500 to get actual day
            return $dayOfYear > 500 ? $dayOfYear - 500 : $dayOfYear;
        } else {
            $dayCode = (int)substr($trimmedNIC, 4, 3);
            // For new format, if dayCode >= 500, subtract 500
            return $dayCode >= 500 ? $dayCode - 500 : $dayCode;
        }
    }

    /**
     * Get birth month from NIC
     * @param string $nic NIC number
     * @return int|null Birth month (1-12) or null if invalid
     */
    public static function getNICBirthMonth(string $nic): ?int
    {
        $dayOfYear = self::getNICDayOfYear($nic);
        if ($dayOfYear === null) {
            return null;
        }

        $dateInfo = DayLK::toDate($dayOfYear);
        return $dateInfo['month'];
    }

    /**
     * Get birth day from NIC
     * @param string $nic NIC number
     * @return int|null Birth day (1-31) or null if invalid
     */
    public static function getNICBirthDay(string $nic): ?int
    {
        $dayOfYear = self::getNICDayOfYear($nic);
        if ($dayOfYear === null) {
            return null;
        }

        $dateInfo = DayLK::toDate($dayOfYear);
        return $dateInfo['day'];
    }

    /**
     * Simple format validation only (no logical checks)
     * @param string $nic NIC number to validate
     * @return bool true if format is valid, false otherwise
     */
    public static function isSimpleValidNIC(string $nic): bool
    {
        if (!is_string($nic)) {
            return false;
        }
        $trimmedNIC = trim($nic);
        return preg_match(self::OLD_NIC_REGEX, $trimmedNIC) || preg_match(self::NEW_NIC_REGEX, $trimmedNIC);
    }

    /**
     * Full validation including format and logical birth values (day, year ranges)
     * @param string $nic NIC number to validate
     * @return ValidationResult with isValid and errorReason
     */
    public static function isFullValidNIC(string $nic): ValidationResult
    {
        $result = self::validateNIC($nic);
        return new ValidationResult(
            isValid: $result->isValid,
            errorReason: $result->error ?? null
        );
    }

    /**
     * Extract detailed information from NIC
     * @param string $nic NIC number
     * @return NICDetails with all extracted information
     */
    public static function getNICDetails(string $nic): NICDetails
    {
        if (!is_string($nic) || $nic === '') {
            return new NICDetails(
                isValid: false,
                nicNumber: $nic ?? '',
                normalizedNIC: null,
                format: NICFormat::INVALID,
                errorReason: 'NIC must be a non-empty string',
                birthDate: null,
                birthYear: null,
                birthMonth: null,
                birthDay: null,
                dayOfYear: null,
                gender: null
            );
        }

        $trimmedNIC = trim($nic);
        $format = self::validateFormat($trimmedNIC);

        if (!$format) {
            return new NICDetails(
                isValid: false,
                nicNumber: $trimmedNIC,
                normalizedNIC: null,
                format: NICFormat::INVALID,
                errorReason: 'Invalid NIC format. Must be 9 digits + V/X (old) or 12 digits (new)',
                birthDate: null,
                birthYear: null,
                birthMonth: null,
                birthDay: null,
                dayOfYear: null,
                gender: null
            );
        }

        // Extract day of year
        $dayOfYear = 0;
        $gender = null;

        if ($format === NICFormat::OLD) {
            $dayCode = (int)substr($trimmedNIC, 2, 3);
            $dayOfYear = $dayCode > 500 ? $dayCode - 500 : $dayCode;
            // $gender = $dayCode % 2 === 1 ? Gender::MALE : Gender::FEMALE;
$gender = $dayCode < 500 ? Gender::MALE : Gender::FEMALE;
        } else {
            $dayCode = (int)substr($trimmedNIC, 4, 3);
            $dayOfYear = $dayCode >= 500 ? $dayCode - 500 : $dayCode;
            $gender = $dayCode < 500 ? Gender::MALE : Gender::FEMALE;
        }

        // Validate day of year
        if ($dayOfYear < 1 || $dayOfYear > 366) {
            return new NICDetails(
                isValid: false,
                nicNumber: $trimmedNIC,
                normalizedNIC: null,
                format: $format,
                errorReason: "Invalid day of year: {$dayOfYear}. Must be between 1 and 366",
                birthDate: null,
                birthYear: null,
                birthMonth: null,
                birthDay: null,
                dayOfYear: null,
                gender: null
            );
        }

        // Extract birth year
        $birthYear = 0;
        if ($format === NICFormat::OLD) {
            $yearDigits = (int)substr($trimmedNIC, 0, 2);
            $birthYear = 1900 + $yearDigits;
        } else {
            $birthYear = (int)substr($trimmedNIC, 0, 4);
        }

        // Validate year
        $currentYear = DayLK::now()['year'];
        if ($birthYear < 1900 || $birthYear > $currentYear) {
            return new NICDetails(
                isValid: false,
                nicNumber: $trimmedNIC,
                normalizedNIC: null,
                format: $format,
                errorReason: "Invalid birth year: {$birthYear}. Must be between 1900 and {$currentYear}",
                birthDate: null,
                birthYear: null,
                birthMonth: null,
                birthDay: null,
                dayOfYear: null,
                gender: null
            );
        }

        // Convert day of year to month and day
        $dateInfo = DayLK::toDate($dayOfYear);
        $birthDate = new \DateTime("{$birthYear}-{$dateInfo['month']}-{$dateInfo['day']}");

        // Calculate age
        $today = new \DateTime();
        $age = $today->format('Y') - $birthYear;
        $birthDateThisYear = new \DateTime("{$today->format('Y')}-{$dateInfo['month']}-{$dateInfo['day']}");
        if ($today < $birthDateThisYear) {
            $age--;
        }

        // Normalize NIC (convert old to new format)
        $normalizedNIC = null;
        if ($format === NICFormat::OLD) {
            $normalizedNIC = self::convertOldToNewNIC($trimmedNIC);
        } else {
            $normalizedNIC = $trimmedNIC;
        }

        return new NICDetails(
            isValid: true,
            nicNumber: $trimmedNIC,
            normalizedNIC: $normalizedNIC,
            format: $format,
            errorReason: null,
            birthDate: $birthDate,
            birthYear: $birthYear,
            birthMonth: $dateInfo['month'],
            birthDay: $dateInfo['day'],
            dayOfYear: $dayOfYear,
            gender: $gender,
            age: $age
        );
    }

    /**
     * Main validation function
     * @param string $nic NIC number to validate
     * @param ValidationOptions|null $options Validation options
     * @return NICResult
     */
    public static function validateNIC(string $nic, ?ValidationOptions $options = null): NICResult
    {
        $details = self::getNICDetails($nic);

        if (!$details->isValid) {
            return new NICResult(
                isValid: false,
                format: $details->format === NICFormat::INVALID ? null : $details->format,
                error: $details->errorReason ?? 'Unknown error'
            );
        }

        // Check expiry if requested
        if ($options?->checkExpiry && $details->age !== null) {
            $maxAge = $options->maxAge ?? 120;
            if ($details->age > $maxAge) {
                return new NICResult(
                    isValid: false,
                    format: $details->format,
                    birthYear: $details->birthYear,
                    birthMonth: $details->birthMonth,
                    birthDay: $details->birthDay,
                    gender: $details->gender,
                    error: "Person would be over {$maxAge} years old"
                );
            }
        }

        return new NICResult(
            isValid: true,
            format: $details->format,
            birthYear: $details->birthYear,
            birthMonth: $details->birthMonth,
            birthDay: $details->birthDay,
            gender: $details->gender
        );
    }

    /**
     * Simple validation - returns boolean only
     * @param string $nic NIC number to validate
     * @return bool
     */
    public static function isValidNIC(string $nic): bool
    {
        return self::validateNIC($nic)->isValid;
    }

    /**
     * Extract detailed information from NIC (legacy alias for getNICDetails)
     * @param string $nic NIC number
     * @return array|null NIC details or null if invalid
     * @deprecated Use getNICDetails instead which returns full details even for invalid NICs
     */
    public static function extractNICDetails(string $nic): ?array
    {
        $details = self::getNICDetails($nic);
        if (!$details->isValid) {
            return null;
        }

        return [
            'birthDate' => $details->birthDate,
            'birthYear' => $details->birthYear,
            'birthMonth' => $details->birthMonth,
            'birthDay' => $details->birthDay,
            'dayOfYear' => $details->dayOfYear,
            'gender' => $details->gender,
            'age' => $details->age,
        ];
    }
}