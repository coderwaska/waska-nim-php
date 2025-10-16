<?php

use PHPUnit\Framework\TestCase;
use Wastukancana\Nim;
use Wastukancana\Student;

final class NimParserTest extends TestCase
{
    const NIM_TEST = '211351143';

    const EXPECTED_NAME = 'SULUH SULISTIAWAN';

    const EXPECTED_GENDER = 'M';

    const EXPECTED_GRADUATION = false;

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
        $student->name = self::EXPECTED_NAME;
        $student->gender = self::EXPECTED_GENDER;
        $student->isGraduated = self::EXPECTED_GRADUATION;
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
        $this->assertEquals(self::EXPECTED_NAME, $this->nim->getName());
    }

    public function test_can_get_gender()
    {
        $this->assertEquals(self::EXPECTED_GENDER, $this->nim->getGender());
    }

    public function test_can_get_is_graduated()
    {
        $this->assertEquals(self::EXPECTED_GRADUATION, $this->nim->getIsGraduated());
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

    public function test_nim_with_too_short_length_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Nim('1');
    }

    public function test_nim_with_too_long_length_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Nim('21135114300');
    }

    public function test_nim_with_non_numeric_characters_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Nim('2113511a3');
    }

    public function test_invalid_admission_year_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Nim('991351143');
    }

    public function test_non_existent_study_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Nim('210001143');
    }
}
