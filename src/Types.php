<?php

namespace Teknyo\NICValidator;

/**
 * Type definitions for Sri Lanka NIC validator
 */

/**
 * Result of NIC validation and extraction
 */
class NICResult
{
    public function __construct(
        /** Whether the NIC is valid */
        public readonly bool $isValid,
        /** NIC format detected */
        public readonly ?NICFormat $format = null,
        /** Birth year (4 digits) */
        public readonly ?int $birthYear = null,
        /** Birth month (1-12) */
        public readonly ?int $birthMonth = null,
        /** Birth day (1-31) */
        public readonly ?int $birthDay = null,
        /** Gender */
        public readonly Gender|null $gender = null,
        /** Error message if invalid */
        public readonly ?string $error = null,
    ) {}
}

/**
 * Detailed NIC information
 */
class NICDetails
{
    public function __construct(
        /** Whether the NIC is valid */
        public readonly bool $isValid,
        /** Original NIC number provided */
        public readonly string $nicNumber,
        /** Normalized NIC (converts old NIC to new format YYYYDDDNNNN if valid, null otherwise) */
        public readonly ?string $normalizedNIC,
        /** Format detected: 'old', 'new', or 'invalid' */
        public readonly NICFormat $format,
        /** Error reason if invalid, null if valid */
        public readonly ?string $errorReason,
        /** Full birth date */
        public readonly ?\DateTime $birthDate,
        /** Birth year (4 digits) */
        public readonly ?int $birthYear,
        /** Birth month (1-12) */
        public readonly ?int $birthMonth,
        /** Birth day (1-31) */
        public readonly ?int $birthDay,
        /** Day of year (1-366) */
        public readonly ?int $dayOfYear,
        /** Gender */
        public readonly Gender|null $gender,
        /** Age in years (optional, calculated when needed) */
        public readonly ?int $age = null,
    ) {}
}

/**
 * Validation result with error reason
 */
class ValidationResult
{
    public function __construct(
        /** Whether the NIC is valid */
        public readonly bool $isValid,
        /** Error reason if invalid, null if valid */
        public readonly ?string $errorReason,
    ) {}
}

/**
 * Validation options
 */
class ValidationOptions
{
    public function __construct(
        /** Check if NIC has expired (person would be over maxAge) */
        public readonly bool $checkExpiry = false,
        /** Maximum age to consider valid (default: 120) */
        public readonly int $maxAge = 120,
    ) {}
}