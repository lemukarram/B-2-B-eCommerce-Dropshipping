<?php

declare(strict_types=1);

namespace Core;

/**
 * Rule-based input validator.
 *
 * Usage:
 *   $v = new Validator($request->all(), [
 *       'email'         => 'required|email|max:255',
 *       'selling_price' => 'required|decimal|min:0.01',
 *       'phone'         => 'required|regex:/^03[0-9]{9}$/',
 *       'role'          => 'required|in:admin,seller',
 *   ]);
 *
 *   if (!$v->passes()) {
 *       // $v->errors() → ['field' => ['Error message', ...]]
 *   }
 *
 * Available rules:
 *   required, string, integer, decimal, email, url,
 *   min:N, max:N, in:a,b,c, regex:/pattern/,
 *   confirmed (field_confirmation must match)
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    public function passes(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            
            // Split by pipe but ignore pipes inside regex patterns /.../
            $rules = preg_split('/\|(?![^/]*\/)/', $ruleString);

            foreach ($rules as $rule) {
                [$name, $param] = $this->parseRule($rule);
                $error = $this->applyRule($field, $value, $name, $param);
                if ($error !== null) {
                    $this->errors[$field][] = $error;
                    // Stop checking this field after first failure
                    // (unless you want all errors — remove this break)
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function parseRule(string $rule): array
    {
        if (!str_contains($rule, ':')) {
            return [$rule, null];
        }
        $pos = strpos($rule, ':');
        return [substr($rule, 0, $pos), substr($rule, $pos + 1)];
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        return match ($rule) {
            'required' => (
                $value === null || $value === ''
                    ? "{$label} is required."
                    : null
            ),
            'string' => (
                $value !== null && !is_string($value)
                    ? "{$label} must be a string."
                    : null
            ),
            'integer' => (
                $value !== null && $value !== '' &&
                filter_var($value, FILTER_VALIDATE_INT) === false
                    ? "{$label} must be an integer."
                    : null
            ),
            'decimal' => (
                $value !== null && $value !== '' &&
                filter_var($value, FILTER_VALIDATE_FLOAT) === false
                    ? "{$label} must be a valid number."
                    : null
            ),
            'email' => (
                $value !== null && $value !== '' &&
                filter_var($value, FILTER_VALIDATE_EMAIL) === false
                    ? "{$label} must be a valid email address."
                    : null
            ),
            'url' => (
                $value !== null && $value !== '' &&
                filter_var($value, FILTER_VALIDATE_URL) === false
                    ? "{$label} must be a valid URL."
                    : null
            ),
            'min' => (
                $value !== null && $value !== '' &&
                (is_numeric($value)
                    ? (float)$value < (float)$param
                    : mb_strlen((string)$value) < (int)$param)
                    ? (is_numeric($value)
                        ? "{$label} must be at least {$param}."
                        : "{$label} must be at least {$param} characters.")
                    : null
            ),
            'max' => (
                $value !== null && $value !== '' &&
                (is_numeric($value)
                    ? (float)$value > (float)$param
                    : mb_strlen((string)$value) > (int)$param)
                    ? (is_numeric($value)
                        ? "{$label} may not be greater than {$param}."
                        : "{$label} may not exceed {$param} characters.")
                    : null
            ),
            'in' => (
                $value !== null && $value !== '' &&
                !in_array($value, explode(',', $param ?? ''), true)
                    ? "{$label} must be one of: {$param}."
                    : null
            ),
            'regex' => (
                $value !== null && $value !== '' &&
                !preg_match($param, (string)$value)
                    ? "{$label} format is invalid."
                    : null
            ),
            'confirmed' => (
                ($this->data[$field . '_confirmation'] ?? null) !== $value
                    ? "{$label} confirmation does not match."
                    : null
            ),
            default => null,
        };
    }
}
