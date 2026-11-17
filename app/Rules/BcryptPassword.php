<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BcryptPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (str_contains($value, "\0")) {
            $fail('Password tidak boleh mengandung karakter null.');
        }

        if (strlen($value) > 72) {
            $fail('Password maksimal 72 byte; karakter seperti emoji dapat memakai lebih dari satu byte.');
        }
    }
}
