<?php

declare(strict_types=1);

namespace App\Model\Form;

use App\Entity\Serializable;
use JMS\Serializer\Annotation\Expose;
use OpenApi\Attributes as OA;

class FormErrorModel implements Serializable
{
    public function __construct()
    {
        $this->reason = new FormErrorDetailModel();
    }

    #[Expose]
    #[OA\Property(description: 'HTTP status code', type: 'int', example: '400')]
    private ?int $code = null;

    #[Expose]
    private FormErrorDetailModel $reason;

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getReason(): FormErrorDetailModel
    {
        return $this->reason;
    }

    public function setReason(FormErrorDetailModel $reason): self
    {
        $this->reason = $reason;
        return $this;
    }
}
