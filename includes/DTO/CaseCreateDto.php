<?php
namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * DTO for creating a new case.
 */
class CaseCreateDto extends BaseDto {
    
    /**
     * @var int User ID.
     */
    private int $userId;
    
    /**
     * @var string Case title.
     */
    private string $title;
    
    /**
     * @var string Case status.
     */
    private string $status = 'open';
    
    /**
     * @var string Case description.
     */
    private string $description = '';
    
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
     * @param array $data Case data.
     * @throws ValidationException If validation fails.
     */
    public function __construct(array $data) {
        $this->validateRequired($data, ['id_user', 'title']);
        
        $this->userId = $this->validateInt($data['id_user'], 'id_user', true, 1);
        $this->title = $this->validateString($data['title'], 'title', true, 1, 255);
        $this->status = $this->validateString($data['status'] ?? 'open', 'status', false, 1, 50) ?? 'open';
        $this->description = $this->validateString($data['description'] ?? '', 'description', false, 0, 2000) ?? '';
        
        // Validate dates if provided
        if (isset($data['start_at']) && $data['start_at'] !== '') {
            $this->startAt = $this->validateDate($data['start_at'], 'start_at', false);
        }
        
        if (isset($data['due_at']) && $data['due_at'] !== '') {
            $this->dueAt = $this->validateDate($data['due_at'], 'due_at', false);
        }
        
        // Validate date range
        $this->validateDateRange($this->startAt, $this->dueAt);
        
        // Validate owner ID if provided
        if (isset($data['owner_id']) && $data['owner_id'] !== '') {
            $this->ownerId = $this->validateInt($data['owner_id'], 'owner_id', false, 1);
        }
    }
    
    /**
     * Get user ID.
     *
     * @return int
     */
    public function getUserId(): int {
        return $this->userId;
    }
    
    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }
    
    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus(): string {
        return $this->status;
    }
    
    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription(): string {
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
     * Convert to array for database insertion.
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'id_user' => $this->userId,
            'title' => $this->title,
            'status' => $this->status,
            'description' => $this->description,
        ];
        
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