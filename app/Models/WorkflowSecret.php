<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WorkflowSecret extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'encrypted_value',
        'description',
    ];

    protected $hidden = [
        'encrypted_value',
    ];

    public function setValue(string $value): void
    {
        $this->encrypted_value = Crypt::encryptString($value);
    }

    public function getValue(): string
    {
        return Crypt::decryptString($this->encrypted_value);
    }
}
