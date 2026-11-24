<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'type',
        'subject',
        'product_name',
        'message',
        'details',
        'status',
        'admin_notes',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    /**
     * Chronological list of admin email replies for this enquiry.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(InquiryReply::class)->orderBy('created_at')->orderBy('id');
    }

    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['new', 'pending'], true);
    }

    public function isReplied(): bool
    {
        return $this->status === 'replied';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isContacted(): bool
    {
        return $this->status === 'contacted';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Human-readable badge label for the current enquiry status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'new', 'pending' => 'Pending',
            'replied' => 'Replied',
            'failed' => 'Failed',
            'contacted' => 'Contacted',
            'closed' => 'Closed',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Resolve a clean subject line from the stored subject, product name, or enquiry type.
     */
    public function resolvedSubject(): string
    {
        $explicit = trim((string) ($this->subject ?? ''));
        if ($explicit !== '') {
            return $explicit;
        }

        if (! empty($this->product_name)) {
            return "Enquiry regarding {$this->product_name}";
        }

        $typeLabel = trim(str_replace('_', ' ', (string) ($this->type ?: 'General')));

        return ucwords($typeLabel).' Enquiry';
    }
}
