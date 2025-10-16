<?php

use PHPUnit\Framework\TestCase;
use Wastukancana\Nim;
use Wastukancana\Student;
use Wastukancana\StudentProviderInterface;

final class NimParserTest extends TestCase
{
    const NIM_TEST = '211351143';

    const EXPECTED_YEAR = 2021;

    const EXPECTED_STUDY = 'Teknik Informatika';

    const EXPECTED_LEVEL = 'S1';

    const EXPECTED_SEMESTER = 1;

    const EXPECTED_SEQUENCE = 143;

    private Nim $nim;

    protected function setUp(): void
    {
        $this->nim = new Nim(self::NIM_TEST);
    }

    public function test_is_valid_admission_year()
    {
        $this->assertTrue($this->nim->isValidAdmissionYear());
    }

    public function test_is_valid_study()
    {
        $this->assertTrue($this->nim->isValidStudy());
    }

    public function test_can_dump()
    {
        $dump = $this->nim->dump();
        $student = new Student;

        $student->nim = self::NIM_TEST;
        $student->name = null;
        $student->gender = null;
        $student->isGraduated = null;
        $student->admissionYear = self::EXPECTED_YEAR;
        $student->study = self::EXPECTED_STUDY;
        $student->educationLevel = self::EXPECTED_LEVEL;
        $student->firstSemester = self::EXPECTED_SEMESTER;
        $student->sequenceNumber = self::EXPECTED_SEQUENCE;

        $this->assertEquals($student, $dump);
    }

    public function test_can_get_nim()
    {
        $this->assertEquals(self::NIM_TEST, $this->nim->getNIM());
    }

    public function test_can_get_name()
    {
        $this->assertNull($this->nim->getName());
    }

    public function test_can_get_gender()
    {
        $this->assertNull($this->nim->getGender());
    }

    public function test_can_get_is_graduated()
    {
        $this->assertNull($this->nim->getIsGraduated());
    }

    public function test_can_get_first_semester()
    {
        $this->assertEquals(self::EXPECTED_SEMESTER, $this->nim->getFirstSemester());
    }

    public function test_can_get_sequence_number()
    {
        $this->assertEquals(self::EXPECTED_SEQUENCE, $this->nim->getSequenceNumber());
    }

    public function test_can_get_admission_year()
    {
        $this->assertEquals(self::EXPECTED_YEAR, $this->nim->getAdmissionYear());
    }

    public function test_can_get_study()
    {
        $this->assertEquals(self::EXPECTED_STUDY, $this->nim->getStudy());
    }

    public function test_can_get_education_level()
    {
        $this->assertEquals(self::EXPECTED_LEVEL, $this->nim->getEducationLevel());
    }

    /**
     * @dataProvider invalidNimsProvider
     */
    public function test_invalid_nims_throw_exception(string $badNim): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Nim($badNim);
    }

    public function invalidNimsProvider(): array
    {
        return [
            'too short' => ['1'],
            'too long' => ['21135114300'],
            'non numeric' => ['2113511a3'],
            'invalid admission year' => ['991351143'],
            'non existent study' => ['210001143'],
        ];
    }

    public function test_can_use_provider_fqcn_without_token(): void
    {
        $nim = new Nim(self::NIM_TEST, FakeProviderForNimTest::class);

        $this->assertSame('FAKE', $nim->getName());
        $this->assertSame('M', $nim->getGender());
        $this->assertFalse($nim->getIsGraduated());
        $this->assertSame([self::NIM_TEST, null], FakeProviderForNimTest::$lastConstructArgs);
    }

    public function test_can_use_provider_fqcn_with_token(): void
    {
        $nim = new Nim(self::NIM_TEST, FakeProviderForNimTest::class, ['token' => 'TOKEN']);

        $this->assertSame('FAKE', $nim->getName());
        $this->assertSame([self::NIM_TEST, 'TOKEN'], FakeProviderForNimTest::$lastConstructArgs);
    }
}

class FakeProviderForNimTest implements StudentProviderInterface
{
    public static $lastConstructArgs = null;

    public function __construct(string $nim, ?string $token = null)
    {
        self::$lastConstructArgs = [$nim, $token];
    }

    public function getName(): ?string
    {
        return 'FAKE';
    }

    public function getGender(): ?string
    {
        return 'M';
    }

    public function getIsGraduated(): ?bool
    {
        return false;
    }
}
