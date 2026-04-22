<?php
namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Base DTO class providing common validation and utility methods.
 */
abstract class BaseDto {
    
    /**
     * Validate that required fields are present.
     *
     * @param array<string, mixed> $data The data to validate.
     * @param array<int, string> $requiredFields List of required field names.
     * @throws ValidationException If any required field is missing.
     */
    protected function validateRequired(array $data, array $requiredFields): void {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                throw new ValidationException("Field '{$field}' is required");
            }
        }
    }
    
    /**
     * Validate integer field.
     *
     * @param mixed $value The value to validate.
     * @param string $fieldName The field name for error messages.
     * @param bool $required Whether the field is required.
     * @param int|null $min Minimum value (inclusive).
     * @param int|null $max Maximum value (inclusive).
     * @return int|null The validated integer or null if not required and empty.
     * @throws ValidationException If validation fails.
     */
    protected function validateInt($value, string $fieldName, bool $required = true, ?int $min = null, ?int $max = null): ?int {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("Field '{$fieldName}' is required");
            }
            return null;
        }
        
        if (!is_numeric($value)) {
            throw new ValidationException("Field '{$fieldName}' must be an integer");
        }
        
        $intValue = (int) $value;
        
        if ($min !== null && $intValue < $min) {
            throw new ValidationException("Field '{$fieldName}' must be at least {$min}");
        }
        
        if ($max !== null && $intValue > $max) {
            throw new ValidationException("Field '{$fieldName}' must be at most {$max}");
        }
        
        return $intValue;
    }
    
    /**
     * Validate string field.
     *
     * @param mixed $value The value to validate.
     * @param string $fieldName The field name for error messages.
     * @param bool $required Whether the field is required.
     * @param int|null $minLength Minimum string length.
     * @param int|null $maxLength Maximum string length.
     * @param string|null $pattern Regex pattern to match.
     * @return string|null The validated string or null if not required and empty.
     * @throws ValidationException If validation fails.
     */
    protected function validateString($value, string $fieldName, bool $required = true, ?int $minLength = null, ?int $maxLength = null, ?string $pattern = null): ?string {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("Field '{$fieldName}' is required");
            }
            return null;
        }
        
        $stringValue = (string) $value;
        
        if ($minLength !== null && mb_strlen($stringValue) < $minLength) {
            throw new ValidationException("Field '{$fieldName}' must be at least {$minLength} characters");
        }
        
        if ($maxLength !== null && mb_strlen($stringValue) > $maxLength) {
            throw new ValidationException("Field '{$fieldName}' must be at most {$maxLength} characters");
        }
        
        if ($pattern !== null && !preg_match($pattern, $stringValue)) {
            throw new ValidationException("Field '{$fieldName}' has invalid format");
        }
        
        return $stringValue;
    }
    
    /**
     * Validate email field.
     *
     * @param mixed $value The value to validate.
     * @param string $fieldName The field name for error messages.
     * @param bool $required Whether the field is required.
     * @return string|null The validated email or null if not required and empty.
     * @throws ValidationException If validation fails.
     */
    protected function validateEmail($value, string $fieldName, bool $required = true): ?string {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("Field '{$fieldName}' is required");
            }
            return null;
        }
        
        $email = sanitize_email($value);
        if (!is_email($email)) {
            throw new ValidationException("Field '{$fieldName}' must be a valid email address");
        }
        
        return $email;
    }
    
    /**
     * Validate date field.
     *
     * @param mixed $value The value to validate.
     * @param string $fieldName The field name for error messages.
     * @param bool $required Whether the field is required.
     * @param string $format Expected date format (default: 'Y-m-d H:i:s').
     * @return string|null The validated date string or null if not required and empty.
     * @throws ValidationException If validation fails.
     */
    protected function validateDate($value, string $fieldName, bool $required = true, string $format = 'Y-m-d H:i:s'): ?string {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("Field '{$fieldName}' is required");
            }
            return null;
        }
        
        $dateString = (string) $value;
        $date = \DateTime::createFromFormat($format, $dateString);
        
        if (!$date || $date->format($format) !== $dateString) {
            throw new ValidationException("Field '{$fieldName}' must be in format: {$format}");
        }
        
        return $dateString;
    }
    
    /**
     * Validate that start date is before or equal to end date.
     *
     * @param string|null $startDate Start date string.
     * @param string|null $endDate End date string.
     * @param string $format Date format.
     * @throws ValidationException If start date is after end date.
     */
    protected function validateDateRange(?string $startDate, ?string $endDate, string $format = 'Y-m-d H:i:s'): void {
        if ($startDate && $endDate) {
            $start = \DateTime::createFromFormat($format, $startDate);
            $end = \DateTime::createFromFormat($format, $endDate);
            
            if ($start > $end) {
                throw new ValidationException("start_at must be before or equal to due_at");
            }
        }
    }
    
    /**
     * Convert DTO to array for database operations.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
    
    /**
     * Get validation errors without throwing exceptions.
     *
     * @return array<int, string> List of validation errors.
     */
    public function getValidationErrors(): array {
        try {
            // Trigger validation by calling toArray
            $this->toArray();
            return [];
        } catch (ValidationException $e) {
            return [$e->getMessage()];
        }
    }
}