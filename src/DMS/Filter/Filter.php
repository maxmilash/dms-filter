<?php

namespace DMS\Filter;

/**
 * Filter Object, responsible for retrieving the filtering rules
 * for the object and applying them
 *
 * @package DMS
 * @subpackage Filter
 *
 */
use DMS\Filter\Filters\Loader\FilterLoaderInterface;
use DMS\Filter\Mapping\ClassMetadataFactoryInterface;

/**
 * Class Filter
 *
 * Executor, receives objects that need filtering and executes attached rules.
 *
 * @package DMS\Filter
 */
class Filter implements FilterInterface
{
    /**
     *
     * @var Mapping\ClassMetadataFactory
     */
    protected Mapping\ClassMetadataFactory $metadataFactory;

    /**
     * @var FilterLoaderInterface
     */
    protected FilterLoaderInterface $filterLoader;

    /**
     * Constructor
     *
     * @param Mapping\ClassMetadataFactory $metadataFactory
     * @param FilterLoaderInterface $filterLoader
     */
    public function __construct(Mapping\ClassMetadataFactory $metadataFactory, $filterLoader)
    {
        $this->metadataFactory = $metadataFactory;
        $this->filterLoader    = $filterLoader;
    }

    /**
     * {@inheritDoc}
     */
    public function filterEntity($object): void
    {
        $this->walkObject($object);
    }

    /**
     * {@inheritDoc}
     */
    public function filterProperty($object, $property): void
    {
        $this->walkObject($object, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function filterValue($value, $rule)
    {
        if ($rule instanceof Rules\Rule) {
            $filter = $this->filterLoader->getFilterForRule($rule);
            return $filter->apply($rule, $value);
        }

        return $this->walkRuleChain($value, $rule);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFactory(): ClassMetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    /**
     * Iterates over annotated properties in an object filtering the selected
     * values
     *
     * @param object $object
     * @param string $limitProperty
     *
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    protected function walkObject($object, $limitProperty = null): void
    {
        if ($object === null) {
            return;
        }

        $metadata = $this->metadataFactory->getClassMetadata(get_class($object));

        //Get a Object Handler/Walker
        $walker = new ObjectWalker($object, $this->filterLoader);

        //Get all filtered properties or limit with selected
        $properties = ($limitProperty !== null) ? [$limitProperty] : $metadata->getFilteredProperties();

        //Iterate over properties with filters
        foreach ($properties as $property) {
            $walker->applyFilterRules($property, $metadata->getPropertyRules($property));
        }
    }

    /**
     * Iterates over an array of filters applying all to the value
     *
     * @param mixed $value
     * @param array $rules
     * @return mixed
     */
    protected function walkRuleChain($value, $rules)
    {
        foreach ($rules as $rule) {
            $filter = $this->filterLoader->getFilterForRule($rule);
            $value = $filter->apply($rule, $value);
        }

        return $value;
    }
}
