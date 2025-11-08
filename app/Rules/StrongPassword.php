<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule untuk memvalidasi kekuatan password.
 * Password harus memiliki minimal 8 karakter, kombinasi huruf besar, kecil, dan angka.
 */
final class StrongPassword implements ValidationRule
{
    /**
     * Minimal panjang password.
     */
    protected int $minLength = 8;

    /**
     * Apakah memerlukan huruf besar.
     */
    protected bool $requireUppercase = true;

    /**
     * Apakah memerlukan huruf kecil.
     */
    protected bool $requireLowercase = true;

    /**
     * Apakah memerlukan angka.
     */
    protected bool $requireNumbers = true;

    /**
     * Apakah memerlukan karakter spesial.
     */
    protected bool $requireSpecialChars = false;

    /**
     * Create a new rule instance.
     *
     * @param  int  $minLength  Minimal panjang password
     * @param  bool  $requireSpecialChars  Apakah memerlukan karakter spesial
     */
    public function __construct(int $minLength = 8, bool $requireSpecialChars = false)
    {
        $this->minLength = $minLength;
        $this->requireSpecialChars = $requireSpecialChars;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Password harus berupa teks.');

            return;
        }

        // Check minimum length
        if (strlen($value) < $this->minLength) {
            $fail("Password minimal harus {$this->minLength} karakter.");

            return;
        }

        // Check for uppercase letter
        if ($this->requireUppercase && ! preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung minimal satu huruf besar.');

            return;
        }

        // Check for lowercase letter
        if ($this->requireLowercase && ! preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung minimal satu huruf kecil.');

            return;
        }

        // Check for number
        if ($this->requireNumbers && ! preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung minimal satu angka.');

            return;
        }

        // Check for special characters
        if ($this->requireSpecialChars && ! preg_match('/[^A-Za-z0-9]/', $value)) {
            $fail('Password harus mengandung minimal satu karakter spesial.');

            return;
        }
    }
}
