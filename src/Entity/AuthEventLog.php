<?php

namespace App\Entity;

use App\Repository\AuthEventLogRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'auth_log')]
#[ORM\Index(columns: ['created_at'], name: 'idx_authlog_created_at')]
#[ORM\Index(columns: ['action'], name: 'idx_authlog_action')]
#[ORM\Index(columns: ['ip'], name: 'idx_authlog_ip')]
#[ORM\Index(columns: ['identifier'], name: 'idx_authlog_identifier')]
#[ORM\Entity(repositoryClass: AuthEventLogRepository::class)]
class AuthEventLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // e.g. login, register, reset_request, reset_confirm, verify_email
    #[ORM\Column(type: 'string', length: 64)]
    private string $action;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $success = false;

    // what user typed; consider hashing if you want less PII
    #[ORM\Column(type: 'string', length: 190, nullable: true)]
    private ?string $identifier = null;

    // nullable: when user doesn't exist / unknown
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    // machine-friendly codes: bad_credentials, user_not_found, invalid_token, weak_password, rate_limited, csrf, ...
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $failureReason = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $context = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setFailureReason(?string $failureReason): self
    {
        $this->failureReason = $failureReason;
        return $this;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function setContext(?array $context): self
    {
        $this->context = $context;
        return $this;
    }
    public function __construct(string $action)
    {
        $this->action = $action;
        $this->createdAt = new \DateTimeImmutable();
    }
}
