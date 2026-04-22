<?php
namespace STOLMC_Service_Tracker\includes\DTO;

/**
 * Base query DTO for pagination and filtering.
 */
abstract class BaseQueryDto extends BaseDto {
    
    /**
     * @var int Page number (1-based).
     */
    protected int $page = 1;
    
    /**
     * @var int Number of items per page.
     */
    protected int $perPage;
    
    /**
     * @var string Search query.
     */
    protected string $searchQuery = '';
    
    /**
     * Constructor.
     *
     * @param array<string, mixed> $data Query parameters.
     */
    public function __construct(array $data = []) {
        $this->page = $this->validateInt($data['page'] ?? 1, 'page', false, 1) ?? 1;
        $this->perPage = $this->validateInt($data['per_page'] ?? $this->getDefaultPerPage(), 'per_page', false, 1, 100) ?? $this->getDefaultPerPage();
        $this->searchQuery = $this->validateString($data['q'] ?? '', 'q', false) ?? '';
    }
    
    /**
     * Get the default number of items per page.
     *
     * @return int
     */
    abstract protected function getDefaultPerPage(): int;
    
    /**
     * Get page number.
     *
     * @return int
     */
    public function getPage(): int {
        return $this->page;
    }
    
    /**
     * Get items per page.
     *
     * @return int
     */
    public function getPerPage(): int {
        return $this->perPage;
    }
    
    /**
     * Get search query.
     *
     * @return string
     */
    public function getSearchQuery(): string {
        return $this->searchQuery;
    }
    
    /**
     * Get offset for database queries.
     *
     * @return int
     */
    public function getOffset(): int {
        return ($this->page - 1) * $this->perPage;
    }
    
    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'q' => $this->searchQuery,
        ];
    }
}