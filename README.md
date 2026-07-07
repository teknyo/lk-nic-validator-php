# LK NIC Validator PHP

A comprehensive PHP package for validating Sri Lankan National Identity Card (NIC) numbers and extracting birth date and gender information.

## Features

- **Dual Format Support**: Validates both old format (9 digits + V/X) and new format (12 digits)
- **PHP 8.4+ Types**: Full type definitions using PHP's native type system
- **PSR-4 Autoloading**: Compatible with modern PHP autoloading standards
- **Comprehensive Validation**: Format validation, logical birth date checks, and year range validation
- **Detailed Information Extraction**: Get birth date, gender, age, and normalized NIC format
- **PHPUnit Tests**: Comprehensive test suite included

## Installation

```bash
composer require lk-nic-validator-php
```

## Usage

### Basic Validation

```php
use LK\NICValidator\NICValidator;
use LK\NICValidator\ValidationOptions;

// Simple format validation (no logical checks)
NICValidator::isSimpleValidNIC('853456789V'); // true
NICValidator::isSimpleValidNIC('123');        // false

// Full validation with logical checks
$result = NICValidator::isFullValidNIC('198534567890');
var_dump($result->isValid);      // true
var_dump($result->errorReason);  // null

// Standard validation
$validation = NICValidator::validateNIC('853456789V');
var_dump($validation->isValid);    // true
var_dump($validation->format);     // 'old'
var_dump($validation->gender);     // 'male' or 'female'
var_dump($validation->birthYear);  // 1985
```

### Extract Detailed Information

```php
use LK\NICValidator\NICValidator;

$details = NICValidator::getNICDetails('853456789V');

if ($details->isValid) {
    echo $details->gender;         // 'male' or 'female'
    echo $details->birthYear;      // 1985
    echo $details->birthMonth;     // 1-12
    echo $details->birthDay;       // 1-31
    echo $details->dayOfYear;      // 1-366
    echo $details->age;            // Age in years
    echo $details->normalizedNIC;  // '1985345678' (new format)
    echo $details->birthDate->format('Y-m-d'); // Date object
} else {
    echo $details->errorReason;
}
```

### Helper Functions

```php
use LK\NICValidator\NICValidator;

NICValidator::getNICGender('853456789V');      // 'male' | 'female' | null
NICValidator::getNICBirthYear('853456789V');   // 1985 | null
NICValidator::getNICDayOfYear('853456789V');   // 345 | null
NICValidator::getNICBirthMonth('853456789V');  // 12 | null
NICValidator::getNICBirthDay('853456789V');    // 10 | null
NICValidator::convertOldToNewNIC('853456789V'); // '1985345678' | null
```

### Validation with Options

```php
use LK\NICValidator\NICValidator;
use LK\NICValidator\ValidationOptions;

// Check if person would be over a certain age
$options = new ValidationOptions(
    checkExpiry: true,
    maxAge: 120
);

$result = NICValidator::validateNIC('190001154567', $options);
if (!$result->isValid) {
    echo $result->error; // "Person would be over 120 years old"
}
```

## API Reference

### Validation Methods

#### `isSimpleValidNIC(string $nic): bool`

Validates only the format without logical checks.

**Parameters:**
- `$nic`: The NIC number to validate

**Returns:** `true` if format is valid, `false` otherwise

#### `isFullValidNIC(string $nic): ValidationResult`

Performs complete validation including format and logical checks.

**Parameters:**
- `$nic`: The NIC number to validate

**Returns:** `ValidationResult` object with `isValid` and `errorReason`

#### `validateNIC(string $nic, ?ValidationOptions $options = null): NICResult`

Main validation function with optional expiry checking.

**Parameters:**
- `$nic`: The NIC number to validate
- `$options`: Optional validation options

**Returns:** `NICResult` object with validation details

### Information Extraction Methods

#### `getNICDetails(string $nic): NICDetails`

Extracts all available information from the NIC.

**Returns:** `NICDetails` object containing:
- `isValid`: Whether the NIC is valid
- `nicNumber`: Original NIC number
- `normalizedNIC`: NIC in new format (YYYYDDDNNNN)
- `format`: 'old', 'new', or 'invalid'
- `errorReason`: Error message if invalid
- `birthDate`: Full birth date (DateTime object)
- `birthYear`: Birth year (4 digits)
- `birthMonth`: Birth month (1-12)
- `birthDay`: Birth day (1-31)
- `dayOfYear`: Day of year (1-366)
- `gender`: 'male' or 'female'
- `age`: Age in years

#### `getNICGender(string $nic): Gender`

Extracts gender from NIC.

#### `getNICBirthYear(string $nic): ?int`

Extracts birth year from NIC.

#### `getNICDayOfYear(string $nic): ?int`

Extracts day of year from NIC.

#### `getNICBirthMonth(string $nic): ?int`

Extracts birth month from NIC.

#### `getNICBirthDay(string $nic): ?int`

Extracts birth day from NIC.

#### `convertOldToNewNIC(string $nic): ?string`

Converts old format NIC to new format.

## Type Definitions

### NICFormat

```php
type NICFormat = 'old'|'new'|'invalid';
```

### Gender

```php
type Gender = 'male'|'female'|null;
```

### NICResult

```php
class NICResult {
    public readonly bool $isValid;
    public readonly ?NICFormat $format;
    public readonly ?int $birthYear;
    public readonly ?int $birthMonth;
    public readonly ?int $birthDay;
    public readonly Gender $gender;
    public readonly ?string $error;
}
```

### NICDetails

```php
class NICDetails {
    public readonly bool $isValid;
    public readonly string $nicNumber;
    public readonly ?string $normalizedNIC;
    public readonly NICFormat $format;
    public readonly ?string $errorReason;
    public readonly ?\DateTime $birthDate;
    public readonly ?int $birthYear;
    public readonly ?int $birthMonth;
    public readonly ?int $birthDay;
    public readonly ?int $dayOfYear;
    public readonly Gender $gender;
    public readonly ?int $age;
}
```

### ValidationResult

```php
class ValidationResult {
    public readonly bool $isValid;
    public readonly ?string $errorReason;
}
```

### ValidationOptions

```php
class ValidationOptions {
    public readonly bool $checkExpiry;
    public readonly int $maxAge;
    
    public function __construct(
        bool $checkExpiry = false,
        int $maxAge = 120
    ) {}
}
```

## NIC Format Details

### Old Format (Pre-2016)
- 9 digits followed by V or X
- Example: `853456789V`
- First 2 digits: Year (assumed 19xx)
- Next 3 digits: Day of year (001-366 for males, 501-866 for females)
- Next 4 digits: Serial number
- Last letter: Check digit (V or X)

### New Format (2016 onwards)
- 12 digits only
- Example: `198534567890`
- First 4 digits: Full birth year
- Next 3 digits: Day of year (001-366 for males, 501-866 for females)
- Last 5 digits: Serial number

### Gender Detection
- **Old Format**: Odd day code = Male, Even day code = Female
- **New Format**: Day code < 500 = Male, Day code >= 500 = Female

## Examples

### Laravel Integration

```php
use LK\NICValidator\NICValidator;

// In a Form Request
public function rules(): array
{
    return [
        'nic_number' => ['required', 'string'],
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $nic = $this->input('nic_number');
        
        if (!NICValidator::isFullValidNIC($nic)->isValid) {
            $validator->errors()->add(
                'nic_number',
                'The NIC number is invalid.'
            );
        }
    });
}
```

### Symfony Integration

```php
use LK\NICValidator\NICValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NICValidatorConstraint extends Constraint
{
    public $message = 'The NIC number "{{ string }}" is not valid.';
}

class NICValidatorConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!NICValidator::isFullValidNIC($value)->isValid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
```

## Running Tests

```bash
cd packages/php
composer install
composer test
```

Or from the monorepo root:

```bash
npm run php:test
```

## Requirements

- PHP ^8.4
- PHPUnit ^11.0 (for testing)

## License

MIT

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues or questions, please open an issue on the repository.