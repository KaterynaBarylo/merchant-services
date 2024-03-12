<?php

namespace App\Entity;

use App\Repository\MetricRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetricRepository::class)]
class Metric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', unique: true)]
    private $adId;

    #[ORM\Column(type: 'integer')]
    private $impressions;

    #[ORM\Column(type: 'integer')]
    private $clicks;

    #[ORM\Column(type: 'integer')]
    private $uniqueClicks;

    #[ORM\Column(type: 'integer')]
    private $leads;

    #[ORM\Column(type: 'integer')]
    private $conversion;

    #[ORM\Column(type: 'float')]
    private $roi;

    public function __construct(string $adId)
    {
        $this->adId = $adId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdId(): ?int
    {
        return $this->adId;
    }

    public function setAdId(int $adId): self
    {
        $this->adId = $adId;

        return $this;
    }

    public function getImpressions(): ?int
    {
        return $this->impressions;
    }

    public function setImpressions(int $impressions): self
    {
        $this->impressions = $impressions;

        return $this;
    }

    public function getClicks(): ?int
    {
        return $this->clicks;
    }

    public function setClicks(int $clicks): self
    {
        $this->clicks = $clicks;

        return $this;
    }

    public function getUniqueClicks(): ?int
    {
        return $this->uniqueClicks;
    }

    public function setUniqueClicks(int $uniqueClicks): self
    {
        $this->uniqueClicks = $uniqueClicks;

        return $this;
    }

    public function getLeads(): ?int
    {
        return $this->leads;
    }

    public function setLeads(int $leads): self
    {
        $this->leads = $leads;

        return $this;
    }

    public function getConversion(): ?int
    {
        return $this->conversion;
    }

    public function setConversion(int $conversion): self
    {
        $this->conversion = $conversion;

        return $this;
    }

    public function getRoi(): ?float
    {
        return $this->roi;
    }

    public function setRoi(float $roi): self
    {
        $this->roi = $roi;

        return $this;
    }
}
