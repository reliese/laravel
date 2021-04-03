<?php

namespace Reliese\Filter;

use InvalidArgumentException;

/**
 * Class StringFilter
 */
class StringFilter
{
    private bool $defaultToIncluded;

    /**
     * DoctrineAnalyserConfiguration constructor.
     *
     * @param bool $defaultToIncluded
     */
    public function __construct(bool $defaultToIncluded)
    {
        $this->defaultToIncluded = $defaultToIncluded;
    }

    /**
     * 'schema name' => bool where true means it should be included and false means it should be excluded
     *
     * @var bool[] $exactMatchFilters
     */
    private array $exactMatchFilters = [];

    /**
     * '/regex/' => bool where true means it should be included and false means it should be excluded
     *
     * @var bool[]
     */
    private array $regularExpressionFilters = [];

    /**
     * @param string $regularExpression
     * @param bool $isIncluded
     *
     * @return $this
     */
    public function setRegularExpressionFilter(string $regularExpression, bool $isIncluded) : self
    {
        /*
         * Check for invalid regular expression
         */
        if (false === @preg_match($regularExpression, null)) {
            throw new InvalidArgumentException("Invalid regular expression: $regularExpression");
        }
        $this->regularExpressionFilters[$regularExpression] = $isIncluded;
        return $this;
    }

    /**
     * @param string $valueToMatch
     * @param bool $isIncluded
     *
     * @return $this
     */
    public function setMatchFilter(string $valueToMatch, bool $isIncluded) : self
    {
        if (empty($valueToMatch)) {
            throw new InvalidArgumentException("Match filter cannot be empty.");
        }
        $this->exactMatchFilters[$valueToMatch] = $isIncluded;
        return $this;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function isExcluded(string $string) : bool
    {
        return !$this->isIncluded($string);
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function isIncluded(string $string) : bool
    {
        /*
         * Check for exact match
         */
        foreach ($this->exactMatchFilters as $valueToMatch => $isIncluded) {
            if ($string === $valueToMatch) {
                return $isIncluded;
            }
        }

        /*
         * Check for regular expression match
         */
        foreach ($this->regularExpressionFilters as $regularExpression => $isIncluded) {
            if (1 === \preg_match($regularExpression, $string)) {
                return $isIncluded;
            }
        }

        /*
         * Otherwise, return the default outcome
         */
        return $this->defaultToIncluded;
    }
}
