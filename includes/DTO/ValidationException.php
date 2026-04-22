<?php
namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Exception thrown when DTO validation fails.
 */
class ValidationException extends \Exception {
    
    /**
     * @var array Additional validation errors.
     */
    private array $errors = [];
    
    /**
     * Constructor.
     *
     * @param string $message The exception message.
     * @param array $errors Additional validation errors.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previous exception.
     */
    public function __construct(string $message = "", array $errors = [], int $code = 0, ?\Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }
    
    /**
     * Get additional validation errors.
     *
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Create a validation exception from multiple errors.
     *
     * @param array $errors List of error messages.
     * @return self
     */
    public static function fromErrors(array $errors): self {
        $message = count($errors) === 1 ? $errors[0] : 'Multiple validation errors occurred';
        return new self($message, $errors);
    }
}