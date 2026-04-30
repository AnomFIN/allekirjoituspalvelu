<?php
declare(strict_types=1);

/**
 * ValidationService — validate user input server-side.
 */
class ValidationService
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if ($value === null || $value === '') {
            $this->errors[$field] = "$label on pakollinen kenttä.";
        }
        return $this;
    }

    public function maxLength(string $field, string $value, int $max, string $label): self
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "$label saa olla korkeintaan $max merkkiä.";
        }
        return $this;
    }

    public function email(string $field, string $value, string $label): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label ei ole kelvollinen sähköpostiosoite.";
        }
        return $this;
    }

    public function in(string $field, mixed $value, array $allowed, string $label): self
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "$label sisältää virheellisen arvon.";
        }
        return $this;
    }

    public function min(string $field, int|float $value, int|float $min, string $label): self
    {
        if ($value < $min) {
            $this->errors[$field] = "$label pitää olla vähintään $min.";
        }
        return $this;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return reset($this->errors) ?: '';
    }

    /** Static convenience factory. */
    public static function make(): self
    {
        return new self();
    }
}
