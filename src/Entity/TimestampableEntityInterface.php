<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

interface TimestampableEntityInterface
{
    public function getCreatedAt(): ?DateTimeImmutable;
    public function setCreatedAt(?DateTimeImmutable $createdAt): static;

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): static;
    public function getUpdatedAt(): ?DateTimeImmutable;
}
