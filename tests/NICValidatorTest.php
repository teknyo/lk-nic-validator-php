<?php

namespace Teknyo\NICValidator;

require_once __DIR__ . '/../src/DayLK.php';
require_once __DIR__ . '/../src/Types.php';
require_once __DIR__ . '/../src/Enums.php';
require_once __DIR__ . '/../src/NICValidator.php';

use PHPUnit\Framework\TestCase;

class NICValidatorTest extends TestCase
{
    public function testIsSimpleValidNICWithOldFormat(): void
    {
        $this->assertTrue(NICValidator::isSimpleValidNIC('853456789V'));
        $this->assertTrue(NICValidator::isSimpleValidNIC('853456789X'));
        $this->assertTrue(NICValidator::isSimpleValidNIC('853456789v'));
        $this->assertTrue(NICValidator::isSimpleValidNIC('853456789x'));
    }

    public function testIsSimpleValidNICWithNewFormat(): void
    {
        $this->assertTrue(NICValidator::isSimpleValidNIC('198534567890'));
        $this->assertTrue(NICValidator::isSimpleValidNIC('200012345678'));
    }

    public function testIsSimpleValidNICWithInvalidFormat(): void
    {
        $this->assertFalse(NICValidator::isSimpleValidNIC('123'));
        $this->assertFalse(NICValidator::isSimpleValidNIC('85345678V'));
        $this->assertFalse(NICValidator::isSimpleValidNIC('19853456789'));
        $this->assertFalse(NICValidator::isSimpleValidNIC(''));
    }

    public function testConvertOldToNewNIC(): void
    {
        $result = NICValidator::convertOldToNewNIC('853456789V');
        $this->assertEquals('19853456789', $result);
    }

    public function testConvertOldToNewNICWithInvalidInput(): void
    {
        $result = NICValidator::convertOldToNewNIC('invalid');
        $this->assertNull($result);
    }

    public function testGetNICGender(): void
    {
        // Old format - odd day code = male, even = female
        $this->assertEquals(Gender::MALE, NICValidator::getNICGender('853456789V'));
        $this->assertEquals(Gender::FEMALE, NICValidator::getNICGender('858456789V'));

        // New format - day code < 500 = male, >= 500 = female
        $this->assertEquals(Gender::MALE, NICValidator::getNICGender('198534567890'));
        $this->assertEquals(Gender::FEMALE, NICValidator::getNICGender('198584567890'));
    }

    public function testGetNICBirthYear(): void
    {
        $this->assertEquals(1985, NICValidator::getNICBirthYear('853456789V'));
        $this->assertEquals(1985, NICValidator::getNICBirthYear('198534567890'));
        $this->assertEquals(2000, NICValidator::getNICBirthYear('200012345678'));
    }

    public function testGetNICDayOfYear(): void
    {
        $this->assertEquals(345, NICValidator::getNICDayOfYear('853456789V'));
        $this->assertEquals(345, NICValidator::getNICDayOfYear('198534567890'));
    }

    public function testGetNICBirthMonth(): void
    {
        $month = NICValidator::getNICBirthMonth('853456789V');
        $this->assertNotNull($month);
        $this->assertGreaterThan(0, $month);
        $this->assertLessThanOrEqual(12, $month);
    }

    public function testGetNICBirthDay(): void
    {
        $day = NICValidator::getNICBirthDay('853456789V');
        $this->assertNotNull($day);
        $this->assertGreaterThan(0, $day);
        $this->assertLessThanOrEqual(31, $day);
    }

    public function testIsFullValidNIC(): void
    {
        $result = NICValidator::isFullValidNIC('198534567890');
        $this->assertTrue($result->isValid);
        $this->assertNull($result->errorReason);
    }

    public function testIsFullValidNICWithInvalidDay(): void
    {
        $result = NICValidator::isFullValidNIC('198599967890');
        $this->assertFalse($result->isValid);
        $this->assertNotNull($result->errorReason);
    }

    public function testGetNICDetails(): void
    {
        $details = NICValidator::getNICDetails('853456789V');

        $this->assertTrue($details->isValid);
        $this->assertEquals('853456789V', $details->nicNumber);
        $this->assertEquals('19853456789', $details->normalizedNIC);
        $this->assertEquals(NICFormat::OLD, $details->format);
        $this->assertNull($details->errorReason);
        $this->assertNotNull($details->birthDate);
        $this->assertEquals(1985, $details->birthYear);
        $this->assertNotNull($details->birthMonth);
        $this->assertNotNull($details->birthDay);
        $this->assertNotNull($details->dayOfYear);
        $this->assertNotNull($details->gender);
        $this->assertNotNull($details->age);
    }

    public function testGetNICDetailsWithNewFormat(): void
    {
        $details = NICValidator::getNICDetails('198534567890');

        $this->assertTrue($details->isValid);
        $this->assertEquals('198534567890', $details->nicNumber);
        $this->assertEquals('198534567890', $details->normalizedNIC);
        $this->assertEquals(NICFormat::NEW, $details->format);
    }

    public function testGetNICDetailsWithInvalidNIC(): void
    {
        $details = NICValidator::getNICDetails('invalid');

        $this->assertFalse($details->isValid);
        $this->assertEquals(NICFormat::INVALID, $details->format);
        $this->assertNotNull($details->errorReason);
    }

    public function testValidateNICWithOptions(): void
    {
        $options = new ValidationOptions(checkExpiry: true, maxAge: 10);
        $result = NICValidator::validateNIC('198534567890', $options);

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('over', $result->error);
    }

    public function testIsValidNIC(): void
    {
        $this->assertTrue(NICValidator::isValidNIC('853456789V'));
        $this->assertTrue(NICValidator::isValidNIC('198534567890'));
        $this->assertFalse(NICValidator::isValidNIC('invalid'));
    }

    public function testExtractNICDetails(): void
    {
        $details = NICValidator::extractNICDetails('853456789V');

        $this->assertNotNull($details);
        $this->assertArrayHasKey('birthDate', $details);
        $this->assertArrayHasKey('birthYear', $details);
        $this->assertArrayHasKey('gender', $details);
    }

    public function testExtractNICDetailsWithInvalidNIC(): void
    {
        $details = NICValidator::extractNICDetails('invalid');
        $this->assertNull($details);
    }
}