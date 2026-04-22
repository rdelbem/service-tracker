<?php
namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * DTO for updating an existing case.
 */
class CaseUpdateDto extends BaseDto {
    
    /**
     * @var string|null Case title.
     */
    private ?string $title = null;
    
    /**
     * @var string|null Case status.
     */
    private ?string $status = null;
    
    /**
     * @var string|null Case description.
     */
    private ?string $description = null;
    
    /**
     * @var string|null Start date.
     */
    private ?string $startAt = null;
    
    /**
     * @var string|null Due date.
     */
    private ?string $dueAt = null;
    
    /**
     * @var int|null Owner ID.
     */
    private ?int $ownerId = null;
    
    /**
     * Constructor.
     *
     * @param array $data Case update data.
     * @throws ValidationException If validation fails.
     */
    public function __construct(array $data) {
        // Only validate fields that are provided
        if (isset($data['title'])) {
            $this->title = $this->validateString($data['title'], 'title', true, 1, 255);
        }
        
        if (isset($data['status'])) {
            $this->status = $this->validateString($data['status'], 'status', true, 1, 50);
        }
        
        if (isset($data['description'])) {
            $this->description = $this->validateString($data['description'], 'description', false, 0, 2000) ?? '';
        }
        
        // Validate dates if provided
        if (isset($data['start_at'])) {
            $this->startAt = $data['start_at'] === '' ? null : $this->validateDate($data['start_at'], 'start_at', false);
        }
        
        if (isset($data['due_at'])) {
            $this->dueAt = $data['due_at'] === '' ? null : $this->validateDate($data['due_at'], 'due_at', false);
        }
        
        // Validate date range if both dates are provided
        $startAtToValidate = $this->startAt ?? ($data['start_at'] ?? null);
        $dueAtToValidate = $this->dueAt ?? ($data['due_at'] ?? null);
        
        if ($startAtToValidate !== null && $dueAtToValidate !== null) {
            $this->validateDateRange(
                $startAtToValidate === '' ? null : $startAtToValidate,
                $dueAtToValidate === '' ? null : $dueAtToValidate
            );
        }
        
        // Validate owner ID if provided (including empty string to clear it)
        if (array_key_exists('owner_id', $data)) {
            $this->ownerId = $data['owner_id'] === '' ? null : $this->validateInt($data['owner_id'], 'owner_id', false, 1);
        }
    }
    
    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle(): ?string {
        return $this->title;
    }
    
    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus(): ?string {
        return $this->status;
    }
    
    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription(): ?string {
        return $this->description;
    }
    
    /**
     * Get start date.
     *
     * @return string|null
     */
    public function getStartAt(): ?string {
        return $this->startAt;
    }
    
    /**
     * Get due date.
     *
     * @return string|null
     */
    public function getDueAt(): ?string {
        return $this->dueAt;
    }
    
    /**
     * Get owner ID.
     *
     * @return int|null
     */
    public function getOwnerId(): ?int {
        return $this->ownerId;
    }
    
    /**
     * Check if any fields are being updated.
     *
     * @return bool
     */
    public function hasUpdates(): bool {
        return $this->title !== null ||
               $this->status !== null ||
               $this->description !== null ||
               $this->startAt !== null ||
               $this->dueAt !== null ||
               $this->ownerId !== null;
    }
    
    /**
     * Convert to array for database update.
     * Only includes fields that are being updated.
     *
     * @return array
     */
    public function toArray(): array {
        $data = [];
        
        if ($this->title !== null) {
            $data['title'] = $this->title;
        }
        
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        
        if ($this->startAt !== null) {
            $data['start_at'] = $this->startAt;
        }
        
        if ($this->dueAt !== null) {
            $data['due_at'] = $this->dueAt;
        }
        
        if ($this->ownerId !== null) {
            $data['owner_id'] = $this->ownerId;
        }
        
        return $data;
    }
}