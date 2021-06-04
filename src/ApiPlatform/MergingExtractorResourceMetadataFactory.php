<?php

declare(strict_types=1);

namespace DavidRoberto\SyliusExtraApiPlugin\ApiPlatform;

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Extractor\ExtractorInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;


class MergingExtractorResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private $extractor;
    private $decorated;

    public function __construct(ExtractorInterface $extractor, ResourceMetadataFactoryInterface $decorated = null)
    {
        $this->extractor = $extractor;
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass): ResourceMetadata
    {
        $parentResourceMetadata = null;
        if ($this->decorated) {
            try {
                $parentResourceMetadata = $this->decorated->create($resourceClass);
            } catch (ResourceClassNotFoundException $resourceNotFoundException) {
                // Ignore not found exception from decorated factories
            }
        }

        if (!(class_exists($resourceClass) || interface_exists($resourceClass)) || !$resource = $this->extractor->getResources()[$resourceClass] ?? false) {
            return $this->handleNotFound($parentResourceMetadata, $resourceClass);
        }

        return $this->update($parentResourceMetadata ?: new ResourceMetadata(), $resource);
    }

    /**
     * Returns the metadata from the decorated factory if available or throws an exception.
     *
     * @param ResourceMetadata|null $parentPropertyMetadata
     * @param string $resourceClass
     * @return ResourceMetadata
     * @throws ResourceClassNotFoundException
     */
    private function handleNotFound(?ResourceMetadata $parentPropertyMetadata, string $resourceClass): ResourceMetadata
    {
        if (null !== $parentPropertyMetadata) {
            return $parentPropertyMetadata;
        }

        throw new ResourceClassNotFoundException(sprintf('Resource "%s" not found.', $resourceClass));
    }

    private function update(ResourceMetadata $resourceMetadata, array $metadata): ResourceMetadata
    {
        foreach (['shortName', 'description', 'iri', 'itemOperations', 'collectionOperations', 'subresourceOperations', 'graphql', 'attributes'] as $propertyName) {
            $propertyValue = $this->resolveResourceMetadataPropertyValue($propertyName, $resourceMetadata, $metadata);
            if (null !== $propertyValue) {
                $resourceMetadata = $resourceMetadata->{'with' . ucfirst($propertyName)}($propertyValue);
            }
        }

        return $resourceMetadata;
    }

    /**
     * @param string $propertyName
     * @param ResourceMetadata $parentResourceMetadata
     * @param array $childResourceMetadata
     * @return mixed
     */
    private function resolveResourceMetadataPropertyValue(
        string $propertyName,
        ResourceMetadata $parentResourceMetadata,
        array $childResourceMetadata
    ) {
        $parentPropertyValue = $parentResourceMetadata->{'get' . ucfirst($propertyName)}();

        $childPropertyValue = $childResourceMetadata[$propertyName];
        if (null === $childPropertyValue) {
            return $parentPropertyValue;
        }

        if (null === $parentPropertyValue) {
            return $childPropertyValue;
        }

        if (is_array($parentPropertyValue)) {
            if (!is_array($childPropertyValue)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid child property value type for property "%s", expected array',
                    $propertyName,
                ));
            }

            return $this->mergeConfigs($parentPropertyValue, $childPropertyValue);
        }

        return $childPropertyValue;
    }

    private function mergeConfigs(...$configs): array
    {
        $resultingConfig = [];

        foreach ($configs as $config) {
            foreach ($config as $newKey => $newValue) {
                $unsetNewKey = false;
                if (is_string($newKey) && 1 === preg_match('/^(.*[^ ]) +\\(unset\\)$/', $newKey, $matches)) {
                    [, $newKey] = $matches;
                    $unsetNewKey = true;
                }

                if ($unsetNewKey) {
                    unset($resultingConfig[$newKey]);

                    if (null === $newValue) {
                        continue;
                    }
                }

                if (is_integer($newKey)) {
                    $resultingConfig[] = $newValue;
                } elseif (isset($resultingConfig[$newKey]) && is_array($resultingConfig[$newKey]) && is_array($newValue)) {
                    $resultingConfig[$newKey] = $this->mergeConfigs($resultingConfig[$newKey], $newValue);
                } else {
                    $resultingConfig[$newKey] = $newValue;
                }
            }
        }

        return $resultingConfig;
    }
}
