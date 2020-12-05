<?php


namespace App\Service;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Class Encacher
 * @package App\Service
 */
class Encacher
{
    private $serializer;
    private $paginationAdder;
    private $cachePool;

    public function __construct(TagAwareCacheInterface $cachePool, SerializerInterface $serializer, PaginationAdder $paginationAdder)
    {
        $this->serializer = $serializer;
        $this->paginationAdder = $paginationAdder;
        $this->cachePool = $cachePool;
    }

    /**
     * @param $entity
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function cacheShow($entity)
    {
        if ($entity===[]) {
            return "[]";
        }

        $entity = is_array($entity) ? $entity[0] : $entity;
        $class = lcfirst(str_replace("App\\Entity\\", "", get_class($entity)));
        $cacheName = $class.$entity->getId();

        return $this->cachePool->get($cacheName, function (ItemInterface $item) use ($entity, $class) {
            $item->expiresAfter(3600);
            $item->tag($class);

            return $this->serializer->serialize($entity, 'json', SerializationContext::create()->setGroups(['detail']));
        });
    }

    /**
     * @param Request $request
     * @param $parameters
     * @param $repository
     * @param int|null $userId
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function cacheIndex(Request $request, $parameters, $repository, ?int $userId = null)
    {
        $class = $userId ? 'customer' : 'phone';
        $cacheName = $class.$request->query->get('page').$request->query->get('brand').$request->query->get('price');

        return $this->cachePool->get($cacheName, function (ItemInterface $item) use ($parameters, $userId, $class, $repository) {
            $item->expiresAfter(3600);
            $item->tag($class);

            if (!$userId) {
                $data = $this->paginationAdder->add($repository->findPhonePaginated($parameters)->getIterator(), $parameters);

                return $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups(['list']));
            }

            $data = $this->paginationAdder->add($repository->findCustomersPaginated($parameters, $userId)->getIterator(), $parameters);

            return $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups(['list']));
        });
    }

    public function Invalidate()
    {
        $this->cachePool->invalidateTags(['customer']);
    }
}
