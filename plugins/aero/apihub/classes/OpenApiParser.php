<?php namespace Aero\ApiHub\Classes;

use Log;

/**
 * OpenAPI Spec Parser
 * Parses OpenAPI/Swagger specifications to extract endpoints
 */
class OpenApiParser
{
    /**
     * Parse OpenAPI spec and extract endpoints
     *
     * @param array $spec OpenAPI specification
     * @return array Parsed endpoints
     */
    public function parseSpec(array $spec): array
    {
        $endpoints = [];

        // Get paths from spec (critical part)
        $paths = $spec['paths'] ?? [];

        if (empty($paths)) {
            Log::warning('OpenAPI spec has no paths', ['spec_keys' => array_keys($spec)]);
            return [];
        }

        // Iterate through each path (e.g., "/users", "/posts/{id}")
        foreach ($paths as $route => $pathItem) {
            // Each path can have multiple HTTP methods
            $methods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];

            foreach ($methods as $method) {
                if (!isset($pathItem[$method])) {
                    continue;
                }

                $operation = $pathItem[$method];

                // Extract endpoint data
                $endpoint = [
                    'route' => $route,
                    'method' => strtoupper($method),
                    'name' => $this->getOperationName($operation, $method, $route),
                    'description' => $this->getDescription($operation),
                    'parameters' => $this->parseParameters($operation, $pathItem),
                    'headers' => $this->parseHeaders($operation),
                    'response_example' => $this->parseResponses($operation),
                ];

                $endpoints[] = $endpoint;
            }
        }

        Log::info('Parsed OpenAPI spec', [
            'total_paths' => count($paths),
            'total_endpoints' => count($endpoints),
        ]);

        return $endpoints;
    }

    /**
     * Get operation name/summary
     *
     * @param array $operation Operation object
     * @param string $method HTTP method
     * @param string $route Route path
     * @return string
     */
    protected function getOperationName(array $operation, string $method, string $route): string
    {
        // Try summary first, then operationId, then generate from method + route
        if (!empty($operation['summary'])) {
            return $operation['summary'];
        }

        if (!empty($operation['operationId'])) {
            return $this->humanizeOperationId($operation['operationId']);
        }

        // Fallback: "GET /users" → "Get Users"
        $routeParts = array_filter(explode('/', $route));
        $lastPart = end($routeParts) ?: 'resource';

        // Remove {params}
        $lastPart = preg_replace('/\{.*?\}/', '', $lastPart);

        return ucfirst($method) . ' ' . ucfirst($lastPart);
    }

    /**
     * Get description
     *
     * @param array $operation Operation object
     * @return string|null
     */
    protected function getDescription(array $operation): ?string
    {
        $description = $operation['description'] ?? $operation['summary'] ?? null;

        if ($description && strlen($description) > 500) {
            return substr($description, 0, 497) . '...';
        }

        return $description;
    }

    /**
     * Parse parameters from operation and path
     *
     * @param array $operation Operation object
     * @param array $pathItem Path item object
     * @return array
     */
    protected function parseParameters(array $operation, array $pathItem): array
    {
        $parameters = [];

        // Path-level parameters (shared by all methods)
        $pathParams = $pathItem['parameters'] ?? [];

        // Operation-level parameters
        $operationParams = $operation['parameters'] ?? [];

        // Merge both (operation params override path params)
        $allParams = array_merge($pathParams, $operationParams);

        foreach ($allParams as $param) {
            $parameters[] = [
                'name' => $param['name'] ?? 'unknown',
                'type' => $this->getParameterType($param),
                'in' => $param['in'] ?? 'query', // query, path, header, cookie
                'required' => $param['required'] ?? false,
                'description' => $param['description'] ?? null,
                'default' => $param['default'] ?? null,
                'example' => $param['example'] ?? null,
            ];
        }

        // Parse requestBody for POST/PUT/PATCH
        if (isset($operation['requestBody'])) {
            $bodyParam = $this->parseRequestBody($operation['requestBody']);
            if ($bodyParam) {
                $parameters[] = $bodyParam;
            }
        }

        return $parameters;
    }

    /**
     * Parse request body
     *
     * @param array $requestBody Request body object
     * @return array|null
     */
    protected function parseRequestBody(array $requestBody): ?array
    {
        $content = $requestBody['content'] ?? [];

        // Get first content type (usually application/json)
        $contentType = array_key_first($content);

        if (!$contentType) {
            return null;
        }

        $schema = $content[$contentType]['schema'] ?? null;

        return [
            'name' => 'body',
            'type' => 'object',
            'in' => 'body',
            'required' => $requestBody['required'] ?? false,
            'description' => $requestBody['description'] ?? 'Request body',
            'content_type' => $contentType,
            'schema' => $schema,
        ];
    }

    /**
     * Parse headers (authentication, etc.)
     *
     * @param array $operation Operation object
     * @return array
     */
    protected function parseHeaders(array $operation): array
    {
        $headers = [];

        // Get security requirements
        $security = $operation['security'] ?? [];

        foreach ($security as $securityItem) {
            foreach ($securityItem as $schemeName => $scopes) {
                $headers[] = [
                    'name' => $this->getSecurityHeaderName($schemeName),
                    'type' => 'string',
                    'required' => true,
                    'description' => "Authentication: {$schemeName}",
                ];
            }
        }

        // Also check for header parameters
        $parameters = $operation['parameters'] ?? [];
        foreach ($parameters as $param) {
            if (($param['in'] ?? '') === 'header') {
                $headers[] = [
                    'name' => $param['name'],
                    'type' => $this->getParameterType($param),
                    'required' => $param['required'] ?? false,
                    'description' => $param['description'] ?? null,
                ];
            }
        }

        return $headers;
    }

    /**
     * Parse responses
     *
     * @param array $operation Operation object
     * @return array|null
     */
    protected function parseResponses(array $operation): ?array
    {
        $responses = $operation['responses'] ?? [];

        // Get successful response (200, 201, etc.)
        $successCodes = ['200', '201', '202', '204'];

        foreach ($successCodes as $code) {
            if (isset($responses[$code])) {
                $response = $responses[$code];

                return [
                    'status' => $code,
                    'description' => $response['description'] ?? null,
                    'content' => $response['content'] ?? null,
                ];
            }
        }

        // Fallback to first response
        if (!empty($responses)) {
            $firstCode = array_key_first($responses);
            $response = $responses[$firstCode];

            return [
                'status' => $firstCode,
                'description' => $response['description'] ?? null,
                'content' => $response['content'] ?? null,
            ];
        }

        return null;
    }

    /**
     * Get parameter type
     *
     * @param array $param Parameter object
     * @return string
     */
    protected function getParameterType(array $param): string
    {
        // Check schema first
        if (isset($param['schema']['type'])) {
            return $param['schema']['type'];
        }

        // Fallback to direct type
        return $param['type'] ?? 'string';
    }

    /**
     * Get security header name based on scheme
     *
     * @param string $schemeName Security scheme name
     * @return string
     */
    protected function getSecurityHeaderName(string $schemeName): string
    {
        $commonMappings = [
            'apiKey' => 'X-API-Key',
            'bearerAuth' => 'Authorization',
            'oauth2' => 'Authorization',
            'basic' => 'Authorization',
        ];

        return $commonMappings[$schemeName] ?? 'Authorization';
    }

    /**
     * Humanize operation ID (camelCase → Title Case)
     *
     * @param string $operationId Operation ID
     * @return string
     */
    protected function humanizeOperationId(string $operationId): string
    {
        // Convert camelCase to words
        $words = preg_split('/(?=[A-Z])/', $operationId, -1, PREG_SPLIT_NO_EMPTY);

        return implode(' ', array_map('ucfirst', $words));
    }

    /**
     * Extract API metadata from spec
     *
     * @param array $spec OpenAPI specification
     * @return array
     */
    public function extractMetadata(array $spec): array
    {
        $info = $spec['info'] ?? [];

        return [
            'title' => $info['title'] ?? 'Unknown API',
            'description' => $info['description'] ?? null,
            'version' => $info['version'] ?? '1.0.0',
            'category' => $info['x-apisguru-categories'][0] ?? 'Other',
            'base_url' => $this->getBaseUrl($spec),
            'contact' => $info['contact'] ?? null,
            'license' => $info['license'] ?? null,
        ];
    }

    /**
     * Get base URL from servers or schemes
     *
     * @param array $spec OpenAPI specification
     * @return string|null
     */
    protected function getBaseUrl(array $spec): ?string
    {
        // OpenAPI 3.x
        if (isset($spec['servers'][0]['url'])) {
            return $spec['servers'][0]['url'];
        }

        // Swagger 2.0
        if (isset($spec['host'])) {
            $scheme = $spec['schemes'][0] ?? 'https';
            $basePath = $spec['basePath'] ?? '';
            return "{$scheme}://{$spec['host']}{$basePath}";
        }

        return null;
    }
}
