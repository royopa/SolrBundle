<?php

namespace FS\SolrBundle\Doctrine\Hydration;

use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;

/**
 * Maps all values of a given document on a target-entity
 */
class ValueHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($document, MetaInformationInterface $metaInformation)
    {
        $targetEntity = $metaInformation->getEntity();

        $reflectionClass = new \ReflectionClass($targetEntity);
        foreach ($document as $property => $value) {
            try {
                $classProperty = $reflectionClass->getProperty($this->removeFieldSuffix($property));
            } catch (\ReflectionException $e) {
                try {
                    $classProperty = $reflectionClass->getProperty(
                        $this->toCamelCase($this->removeFieldSuffix($property))
                    );
                } catch (\ReflectionException $e) {
                    continue;
                }
            }

            $classProperty->setAccessible(true);
            $classProperty->setValue($targetEntity, $value);
        }

        return $targetEntity;
    }

    /**
     * returns the clean fieldname without type-suffix
     *
     * eg: title_s => title
     *
     * @param string $property
     *
     * @return string
     */
    private function removeFieldSuffix($property)
    {
        if (($pos = strrpos($property, '_')) !== false) {
            return substr($property, 0, $pos);
        }

        return $property;
    }

    /**
     * returns field name camelcased if it has underlines
     *
     * eg: user_id => userId
     *
     * @param string $fieldname
     *
     * @return string
     */
    private function toCamelCase($fieldname)
    {
        $words = str_replace('_', ' ', $fieldname);
        $words = ucwords($words);
        $pascalCased = str_replace(' ', '', $words);

        return lcfirst($pascalCased);
    }
} 