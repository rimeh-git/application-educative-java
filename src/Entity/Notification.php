<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
#[ORM\Index(columns: ['session_id'])]
#[ORM\Index(columns: ['type', 'is_read'])]
#[ORM\Index(columns: ['created_at'])]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Session $session = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $type = null; // ABANDONMENT_RISK, SESSION_CANCELLED, PROGRESS_REMINDER, etc.

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $riskScore = null; // 0-100 for abandonment risk

    #[ORM\Column(type: 'json', nullable: true)]
    /** @var array<string, mixed>|null */
    private array|null $metadata = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getRiskScore(): ?float
    {
        return $this->riskScore;
    }

    public function setRiskScore(?float $riskScore): static
    {
        $this->riskScore = max(0, min(100, $riskScore ?? 0));
        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getMetadata(): array|null
    {
        return $this->metadata;
    }

    /** @param array<string, mixed>|null $metadata */
    public function setMetadata(array|null $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        if ($isRead && !$this->readAt) {
            $this->readAt = new DateTimeImmutable();
        }
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReadAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getRiskLevel(): string
    {
        if ($this->riskScore === null) {
            return 'UNKNOWN';
        }
        if ($this->riskScore >= 70) {
            return 'CRITICAL';
        }
        if ($this->riskScore >= 50) {
            return 'HIGH';
        }
        if ($this->riskScore >= 30) {
            return 'MEDIUM';
        }
        return 'LOW';
    }

    public function getRiskColor(): string
    {
        return match ($this->getRiskLevel()) {
            'CRITICAL' => 'red',
            'HIGH' => 'orange',
            'MEDIUM' => 'amber',
            'LOW' => 'green',
            default => 'slate',
        };
    }
}
