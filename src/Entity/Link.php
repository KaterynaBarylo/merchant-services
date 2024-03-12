<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $url;

    #[ORM\Column(unique: true, options: ['collation' => 'utf8_bin'])]
    private string $code;

    #[ORM\Column]
    private int $codeLength;

    #[ORM\Column]
    private int $countOfUsages = 0;

    public function __construct(string $url, string $code)
    {
        $this->url = $url;
        $this->code = $code;
        $this->codeLength = strlen($code);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCountOfUsages(): int
    {
        return $this->countOfUsages;
    }

    public function setCountOfUsages(int $countOfUsages): self
    {
        $this->countOfUsages = $countOfUsages;

        return $this;
    }

    public function incrementCountOfUsages(): self
    {
        ++$this->countOfUsages;

        return $this;
    }

    public function getCodeLength(): int
    {
        return $this->codeLength;
    }

    public function setCodeLength(int $codeLength): self
    {
        $this->codeLength = $codeLength;

        return $this;
    }
}
