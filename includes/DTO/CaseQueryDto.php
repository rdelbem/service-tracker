<?php
namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * DTO for querying cases with pagination and filtering.
 */
class CaseQueryDto extends BaseQueryDto {
    
    /**
     * @var int|null User ID to filter cases by.
     */
    private ?int $userId = null;
    
    /**
     * @var string|null Case status to filter by.
     */
    private ?string $status = null;
    
    /**
     * Constructor.
     *
     * @param array $data Query parameters.
     */
    public function __construct(array $data = []) {
        parent::__construct($data);
        
        $this->userId = $this->validateInt($data['id_user'] ?? null, 'id_user', false, 1) ?? null;
        $this->status = $this->validateString($data['status'] ?? null, 'status', false) ?? null;
    }
    
    /**
     * Get default items per page for cases.
     *
     * @return int
     */
    protected function getDefaultPerPage(): int {
        return 6; // Same as PER_PAGE_DEFAULT in STOLMC_Service_Tracker_Api_Cases
    }
    
    /**
     * Get user ID filter.
     *
     * @return int|null
     */
    public function getUserId(): ?int {
        return $this->userId;
    }
    
    /**
     * Get status filter.
     *
     * @return string|null
     */
    public function getStatus(): ?string {
        return $this->status;
    }
    
    /**
     * Get query parameters for database queries.
     *
     * @return array
     */
    public function getQueryParams(): array {
        $params = [];
        
        if ($this->userId !== null) {
            $params['id_user'] = $this->userId;
        }
        
        if ($this->status !== null) {
            $params['status'] = $this->status;
        }
        
        return $params;
    }
    
    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray(): array {
        return array_merge(parent::toArray(), [
            'id_user' => $this->userId,
            'status' => $this->status,
        ]);
    }
}