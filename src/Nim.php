<?php

namespace Wastukancana;

class Nim extends Parser
{
    private const MIN_YEAR = 2001;

    private PDDikti $pddikti;

    public function __construct($nim)
    {
        parent::__construct($nim);

        $this->isValid();
        $this->pddikti = new PDDikti($this->nim);
    }

    private function isValid(): bool
    {
        if (strlen($this->nim) < 8 || strlen($this->nim) > 9) {
            throw new \InvalidArgumentException('NIM must be 8 or 9 characters');
        }

        if (! ctype_digit($this->nim)) {
            throw new \InvalidArgumentException('NIM must contain only numbers');
        }

        if (! $this->isValidAdmissionYear()) {
            throw new \InvalidArgumentException('Admission year is invalid');
        }

        if (! $this->isValidStudy()) {
            throw new \InvalidArgumentException('Study cannot be found');
        }

        return true;
    }

    public function getName()
    {
        return $this->pddikti->getName();
    }

    public function getGender()
    {
        return $this->pddikti->getGender();
    }

    public function getIsGraduated()
    {
        return $this->pddikti->getIsGraduated();
    }

    public function isValidAdmissionYear(): bool
    {
        $currentYear = intval(date('Y'));
        $admissionYear = $this->getAdmissionYear();

        return $admissionYear >= self::MIN_YEAR && $admissionYear <= $currentYear;
    }

    public function getAdmissionYear(): int
    {
        $year = $this->getAdmissionYearCode();

        return intval('20'.$year);
    }

    public function isValidStudy(): bool
    {
        return array_key_exists($this->getStudyCode(), StudyConfig::STUDIES);
    }

    public function getStudy(): ?string
    {
        return StudyConfig::STUDIES[$this->getStudyCode()]['name'] ?? null;
    }

    public function getEducationLevel(): ?string
    {
        return StudyConfig::STUDIES[$this->getStudyCode()]['level'] ?? null;
    }

    public function dump(): Student
    {
        $student = new Student;
        $student->nim = $this->getNIM();
        $student->name = $this->getName();
        $student->gender = $this->getGender();
        $student->isGraduated = $this->getIsGraduated();
        $student->admissionYear = $this->getAdmissionYear();
        $student->study = $this->getStudy();
        $student->educationLevel = $this->getEducationLevel();
        $student->firstSemester = $this->getFirstSemester();
        $student->sequenceNumber = $this->getSequenceNumber();

        return $student;
    }
}
