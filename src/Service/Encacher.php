<?php


namespace App\Service;

use App\Repository\CustomerRepository;
use App\Repository\PhoneRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class Encacher
 * @package App\Service
 */
class Encacher
{
    private $serializer;
    private $cache;
    private $paginationAdder;
    private $phoneRepository;
    private $customerRepository;

    public function __construct(SerializerInterface $serializer, CacheInterface $cache, PaginationAdder $paginationAdder, PhoneRepository $phoneRepository, CustomerRepository $customerRepository)
    {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->paginationAdder = $paginationAdder;
        $this->phoneRepository = $phoneRepository;
        $this->customerRepository = $customerRepository;
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

        $cacheName = str_replace("App\\Entity\\", "", get_class($entity)).$entity->getId();
        return $this->cache->get($cacheName, function (ItemInterface $item) use ($entity) {
            $item->expiresAfter(3600);

            return $this->serializer->serialize($entity, 'json', SerializationContext::create()->setGroups(['detail']));
        });
    }

    /**
     * @param $request
     * @param $parameters
     * @param int|null $userId
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function cacheIndex(Request $request, $parameters, ?int $userId = null)
    {
        $class = $userId ? 'customer' : 'phone';
        $cacheName = $class.$request->query->get('page').$request->query->get('brand').$request->query->get('price');

        return $this->cache->get($cacheName, function (ItemInterface $item) use ($parameters, $userId) {
            $item->expiresAfter(3600);

            if (!$userId) {
                $data = $this->paginationAdder->add($this->phoneRepository->findPhonePaginated($parameters)->getIterator(), $parameters);

                return $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups(['list']));
            }

            $data = $this->paginationAdder->add($this->customerRepository->findCustomersPaginated($parameters, $userId)->getIterator(), $parameters);

            return $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups(['list']));
        });
    }
}
