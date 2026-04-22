<?php
namespace STOLMC_Service_Tracker\includes\DTO;

use WP_REST_Request;

/**
 * Factory for creating DTOs from WP_REST_Request objects.
 */
class DtoFactory {
    
    /**
     * Create a DTO from WP_REST_Request.
     *
     * @param string $dtoClass The DTO class name.
     * @param WP_REST_Request $request The REST request.
     * @return BaseDto
     * @throws ValidationException If validation fails.
     */
    public static function createFromRequest(string $dtoClass, WP_REST_Request $request): BaseDto {
        // Get all parameters from the request
        $params = array_merge(
            $request->get_url_params(),
            $request->get_query_params(),
            $request->get_body_params()
        );
        
        // Handle JSON body
        $body = $request->get_body();
        if (!empty($body) && $request->get_header('content-type') === 'application/json') {
            $jsonParams = json_decode($body, true);
            if (is_array($jsonParams)) {
                $params = array_merge($params, $jsonParams);
            }
        }
        
        // Create DTO instance
        return new $dtoClass($params);
    }
    
    /**
     * Create a DTO from WP_REST_Request and handle validation errors.
     *
     * @param string $dtoClass The DTO class name.
     * @param WP_REST_Request $request The REST request.
     * @return array [BaseDto|null, array] The DTO and validation errors.
     */
    public static function createFromRequestWithValidation(string $dtoClass, WP_REST_Request $request): array {
        try {
            $dto = self::createFromRequest($dtoClass, $request);
            return [$dto, []];
        } catch (ValidationException $e) {
            $errors = [$e->getMessage()];
            if ($e->getErrors()) {
                $errors = array_merge($errors, $e->getErrors());
            }
            return [null, $errors];
        }
    }
    
    /**
     * Create multiple DTOs from request data.
     *
     * @param string $dtoClass The DTO class name.
     * @param array $dataArray Array of data arrays.
     * @return array Array of DTOs.
     * @throws ValidationException If any validation fails.
     */
    public static function createMultiple(string $dtoClass, array $dataArray): array {
        $dtos = [];
        $errors = [];
        
        foreach ($dataArray as $index => $data) {
            try {
                $dtos[] = new $dtoClass($data);
            } catch (ValidationException $e) {
                $errors[] = "Item {$index}: " . $e->getMessage();
            }
        }
        
        if (!empty($errors)) {
            throw ValidationException::fromErrors($errors);
        }
        
        return $dtos;
    }
}