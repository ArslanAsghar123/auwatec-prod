<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Dal;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregatorResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

abstract class EntityRepository
{

    /**
     * @var \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository
     */
    private $repository;

    private readonly Context $defaultContext;

    /**
     * EntityRepository constructor.
     */
    public function __construct(\Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository)
    {
        $this->repository = $repository;
        $this->defaultContext = Context::createDefaultContext();
    }

    /**
     * @return \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository
     */
    public function getRepository(): \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return EntityDefinition
     */
    public function getDefinition(): EntityDefinition
    {
        return $this->repository->getDefinition();
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function create(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);
        $data = $this->processData($data);

        return $this->repository->create($data, $context);
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function update(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);
        $data = $this->processData($data);

        return $this->repository->update($data, $context);
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function updateByCriteria(array $updateData, Criteria $criteria, ?Context $context = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);
        $result = $this->searchIds($criteria, $context);

        $updates = [];
        foreach($result->getIds() as $id) {
            /** Add the id we want to update */
            $itemUpdate = array_merge($updateData, [ 'id' => $id ]);

            /** Add to queue */
            $updates[] = $itemUpdate;
        }

        return $this->update($updates, $context);
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function upsert(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);
        $data = $this->processData($data);

        return $this->repository->upsert($data, $context);
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function delete(array $data, ?Context $context = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);
        $data = $this->processData($data);

        return $this->repository->delete($data, $context);
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function deleteById(string $entityId, ?Context $context = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);

        return $this->repository->delete([
            [ 'id' => $entityId ]
        ], $context);
    }

    /**
     * @return EntityWrittenContainerEvent|null
     */
    public function deleteByCriteria(Criteria $criteria, ?Context $context = null): ?EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);
        $result = $this->searchIds($criteria, $context);

        $ids = [];
        foreach($result->getIds() as $id) {
            $ids[] = [ 'id' => $id ];
        }

        if(empty($ids)) {
            return null;
        }

        return $this->repository->delete($ids, $context);
    }

    /**
     * @param array|null $associations
     * @param bool $disableCache
     * @return Entity|null
     * @throws InconsistentCriteriaIdsException
     */
    public function get(string $id, array $associations = null, ?Context $context = null, $disableCache = false): ?Entity
    {
        $context = $this->processContext($context);
        $criteria = new Criteria([ $id ]);

        if (is_array($associations)) {
            foreach($associations as $association) {
                $criteria->addAssociation($association);
            }
        }

        /** Ignore the cache if necessary  */
        if (true === $disableCache) {
            $repository = $this->repository;
            return $repository->search(
                $criteria,
                $context
            )->first();
        }

        return $this->repository->search(
            $criteria,
            $context
        )->first();
    }

    /**
     * @param bool $disableCache
     * @return EntitySearchResult
     */
    public function search(Criteria $criteria, ?Context $context = null, $disableCache = false): EntitySearchResult
    {
        $context = $this->processContext($context);

        /** Ignore the cache if necessary  */
        if (true === $disableCache) {
            $repository = $this->repository;
            return $repository->search($criteria, $context);
        }

        return $this->repository->search($criteria, $context);
    }

    /**
     * @param bool $disableCache
     * @return IdSearchResult
     */
    public function searchIds(Criteria $criteria, ?Context $context = null, $disableCache = false): IdSearchResult
    {
        $context = $this->processContext($context);

        /** Ignore the cache if necessary  */
        if (true === $disableCache) {
            $repository = $this->repository;
            return $repository->searchIds($criteria, $context);
        }

        return $this->repository->searchIds($criteria, $context);
    }

    /**
     * @return EntityWrittenContainerEvent
     */
    public function clone(string $id, ?Context $context = null, ?string $newId = null): EntityWrittenContainerEvent
    {
        $context = $this->processContext($context);

        return $this->repository->clone($id, $context, $newId);
    }

    /**
     * @return AggregatorResult
     */
    public function aggregate(Criteria $criteria, ?Context $context = null): AggregatorResult
    {
        $context = $this->processContext($context);

        return $this->repository->aggregate($criteria, $context);
    }

    /**
     * @return string
     */
    public function createVersion(string $id, ?Context $context = null, ?string $name = null, ?string $versionId = null): string
    {
        $context = $this->processContext($context);

        return $this->repository->createVersion($id, $context, $name, $versionId);
    }

    public function merge(string $versionId, ?Context $context = null): void
    {
        $context = $this->processContext($context);

        $this->repository->merge($versionId, $context);
    }

    /**
     * @return Context
     */
    private function processContext(?Context $context): Context
    {
        if(null == $context) {
            $context = $this->defaultContext;
        }

        return $context;
    }

    /**
     * Converts the data, so that we can save it
     *
     * @param array $data
     * @return array
     */
    private function processData($data): array
    {
        foreach($data as $key => $item) {
            /** Convert to array, if its an entity */
            if($item instanceof Entity) {
                $data[$key] = $item->toArray();
            }

            /** Throw an exception, if it's not a array in a array */
            if (!is_array($data[$key])) {
                throw new \RuntimeException('The array $data must contain an other array!');
            }

            /**
             * Convert the entity to an array and clean up all null-value fields,
             * so that the create method of the DAL works correctly
             */
            $data[$key] = $this->unsetNullFields($data[$key]);

            /**
             * Special case:
             * Remove empty translated and extensions keys
             */
            if(isset($data[$key]['translated']) && empty($data[$key]['translated'])) {
                unset($data[$key]['translated']);
            }

            if(isset($data[$key]['extensions']) && empty($data[$key]['extensions'])) {
                unset($data[$key]['extensions']);
            }
        }

        return $data;
    }

    /**
     * @param $array
     * @return array
     */
    private function unsetNullFields($array): array
    {
        foreach($array as $fieldKey => $fieldValue) {
            /** Start the same process for the child entities */
            if($fieldValue instanceof Entity) {
                $array[$fieldKey] = current($this->processData([ $fieldValue ]));
            }

            /** Recursive action  */
            elseif(is_array($fieldValue)) {
                $array[$fieldKey] = $this->unsetNullFields($fieldValue);
            }

            /** Unset field, if its null */
            elseif(null === $fieldValue) {
                unset($array[$fieldKey]);
            }
        }

        return $array;
    }
}
