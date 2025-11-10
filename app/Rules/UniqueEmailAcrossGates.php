<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * Validation rule untuk memastikan email unique across all authentication gates.
 * Mencegah email yang sama digunakan di multiple authentication tables.
 */
final class UniqueEmailAcrossGates implements ValidationRule
{
    /**
     * Tables yang akan dicek untuk duplicate email.
     *
     * @var array<string>
     */
    protected array $tables = ['users', 'staffs'];

    /**
     * ID yang akan dikecualikan dari pengecekan (untuk update).
     */
    protected ?int $ignoreId = null;

    /**
     * Table tempat ID yang dikecualikan berada.
     */
    protected ?string $ignoreTable = null;

    /**
     * Create a new rule instance.
     *
     * @param  string|null  $ignoreTable  Table untuk ignore ID (contoh: 'users', 'staffs')
     * @param  int|null  $ignoreId  ID yang akan dikecualikan
     */
    public function __construct(?string $ignoreTable = null, ?int $ignoreId = null)
    {
        $this->ignoreTable = $ignoreTable;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        foreach ($this->tables as $table) {
            $query = DB::table($table)->where('email', $value);

            // Skip record yang sedang di-update
            if ($this->ignoreTable === $table && $this->ignoreId !== null) {
                $query->where('id', '!=', $this->ignoreId);
            }

            if ($query->exists()) {
                $fail("Email {$attribute} sudah digunakan oleh user lain.");

                return;
            }
        }
    }
}
