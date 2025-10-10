<?php

namespace Wastukancana;

class Parser
{
    protected string $nim;

    public function __construct($nim)
    {
        $this->nim = str_pad(trim($nim), 9, '0', STR_PAD_LEFT);
    }

    public function getNIM(): string
    {
        return ltrim($this->nim, '0');
    }

    public function getAdmissionYearCode(): string
    {
        return substr($this->nim, 0, 2);
    }

    public function getStudyCode(): string
    {
        return substr($this->nim, 2, 3);
    }

    public function getFirstSemester(): int
    {
        return intval(substr($this->nim, 5, 1));
    }

    public function getSequenceNumber(): int
    {
        return intval(substr($this->nim, 6, 3));
    }
}
