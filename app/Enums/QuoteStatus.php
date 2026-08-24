<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case Pending = 'pending';
    case Answered = 'answered';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Get the human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Answered => 'Respondido',
            self::Approved => 'Aprovado',
            self::Rejected => 'Recusado',
            self::Cancelled => 'Cancelado',
        };
    }
}
