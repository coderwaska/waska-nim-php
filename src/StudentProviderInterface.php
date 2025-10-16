<?php

namespace Wastukancana;

interface StudentProviderInterface
{
    public function getName(): ?string;

    public function getGender(): ?string;

    public function getIsGraduated(): ?bool;
}
