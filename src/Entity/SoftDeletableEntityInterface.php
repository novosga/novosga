<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

interface SoftDeletableEntityInterface
{
    public function getDeletedAt(): ?DateTimeImmutable;
    public function setDeletedAt(?DateTimeImmutable $deletedAt): static;
}
