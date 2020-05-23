<?php

namespace DMS\Tests\Dummy\Rules;

use DMS\Filter\Rules\Rule;

class RequiredOptionsRule extends Rule
{
    /**
     * @var mixed
     */
    public $config;
    public string $path;
    public string $url;

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function applyFilter($value)
    {
        return $value;
    }

    public function getDefaultOption(): ?string
    {
        return 'config';
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredOptions(): array
    {
        return ['config', 'path'];
    }
}
