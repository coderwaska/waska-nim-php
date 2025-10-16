<?php

namespace Wastukancana;

use InvalidArgumentException;

class Nim extends Parser
{
    private const MIN_YEAR = 2001;

    private ?StudentProviderInterface $provider = null;

    public function __construct($nim, ?string $provider = null, array $options = [])
    {
        parent::__construct($nim);

        $this->isValid();

        if (is_string($provider)
            && class_exists($provider)
            && is_subclass_of($provider, StudentProviderInterface::class)
        ) {
            if (array_key_exists('token', $options)) {
                $this->provider = new $provider($this->nim, $options['token']);
            } else {
                $this->provider = new $provider($this->nim);
            }
        }
    }

    private function isValid(): bool
    {
        if (strlen($this->nim) < 8 || strlen($this->nim) > 9) {
            throw new InvalidArgumentException('NIM must be 8 or 9 characters');
        }

        if (! ctype_digit($this->nim)) {
            throw new InvalidArgumentException('NIM must contain only numbers');
        }

        if (! $this->isValidAdmissionYear()) {
            throw new InvalidArgumentException('Admission year is invalid');
        }

        if (! $this->isValidStudy()) {
            throw new InvalidArgumentException('Study cannot be found');
        }

        return true;
    }

    public function getName()
    {
        return $this->provider ? $this->provider->getName() : null;
    }

    public function getGender()
    {
        return $this->provider ? $this->provider->getGender() : null;
    }

    public function getIsGraduated()
    {
        return $this->provider ? $this->provider->getIsGraduated() : null;
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
