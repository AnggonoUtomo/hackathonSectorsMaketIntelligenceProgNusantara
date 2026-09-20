<?php

namespace App\Modules\MarketData\Application\DTO;

use App\Modules\MarketData\Application\Exception\InvalidScreenerCriteriaException;

readonly class StructuredScreenerCriteria
{
    /** @var list<array{field: string, operator: string, value: scalar|list<scalar>}> */
    public array $filters;

    /**
     * @param  list<array{field: string, operator: string, value: scalar|list<scalar>}>  $filters
     */
    private function __construct(array $filters, public int $page, public int $perPage)
    {
        $this->filters = $filters;
    }

    /**
     * @param  list<array{field: string, operator: string, value: mixed}>  $filters
     */
    public static function fromFilters(array $filters, int $page = 1, int $perPage = 10): self
    {
        if ($page < 1 || $perPage < 1 || $perPage > 50) {
            throw InvalidScreenerCriteriaException::invalidPagination();
        }

        $normalized = [];

        foreach ($filters as $filter) {
            $field = (string) ($filter['field'] ?? '');
            $operator = (string) ($filter['operator'] ?? '');

            if (! in_array($field, self::allowedFields(), true)) {
                throw InvalidScreenerCriteriaException::unsupportedField($field);
            }

            if (! in_array($operator, self::allowedOperators(), true)) {
                throw InvalidScreenerCriteriaException::unsupportedOperator($operator);
            }

            $value = $filter['value'] ?? null;

            if ($operator === 'in') {
                if (! is_array($value) || $value === []) {
                    throw InvalidScreenerCriteriaException::invalidValue($field);
                }

                $value = array_values($value);

                foreach ($value as $item) {
                    if (! is_scalar($item)) {
                        throw InvalidScreenerCriteriaException::invalidValue($field);
                    }
                }
            } elseif (! is_scalar($value)) {
                throw InvalidScreenerCriteriaException::invalidValue($field);
            }

            /** @var scalar|list<scalar> $value */
            $normalized[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        return new self($normalized, $page, $perPage);
    }

    /**
     * @return array{page: int, per_page: int, query: string}
     */
    public function toQueryParameters(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'query' => json_encode($this->filters, JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @return list<string>
     */
    private static function allowedFields(): array
    {
        return [
            'symbol',
            'sector',
            'sub_sector',
            'industry',
            'market_cap',
            'revenue',
            'net_income',
            'return_on_equity',
            'revenue_growth_yoy',
            'net_income_growth_yoy',
            'pe_ratio',
            'pb_ratio',
        ];
    }

    /**
     * @return list<string>
     */
    private static function allowedOperators(): array
    {
        return ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in'];
    }
}
