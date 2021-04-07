<?php

namespace Reliese\Filter;

use InvalidArgumentException;

/**
 * Class StringFilter
 */
class StringFilter
{
    /**
     * @var bool
     */
    private bool $defaultToIncluded;

    /**
     * @var string[]
     */
    private array $exactMatchExceptions;

    /**
     * @var string[]
     */
    private array $regularExpressionExceptions = [];

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
     * @param string $matchExpression Treated as RegEx if $filter begins with '/^' and ends with '$/'
     *
     * @return $this
     */
    public function addException(string $matchExpression) : static {
        if (empty($matchExpression)) {
            throw new InvalidArgumentException("MatchExpression cannot be empty.");
        }

        if (str_starts_with($matchExpression, '/^') && \str_ends_with($matchExpression, '$/')) {
            $this->regularExpressionExceptions[] = $matchExpression;
        } else {
            $this->exactMatchExceptions[] = $matchExpression;
        }

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
        if ($this->isException($string)) {
            return !$this->defaultToIncluded;
        }
        /*
         * Otherwise, return the default outcome
         */
        return $this->defaultToIncluded;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function isException(string $string) : bool
    {
        if (!empty($this->exactMatchExceptions)) {
            foreach ($this->exactMatchExceptions as $exactMatchException) {
                if ($string === $exactMatchException) {
                    return true;
                }
            }
        }

        if (!empty($this->regularExpressionExceptions)) {
            foreach ($this->regularExpressionExceptions as $regularExpressionException) {
                if (1 === \preg_match($regularExpressionException, $string)) {
                    return true;
                }
            }
        }

        return false;
    }

}
