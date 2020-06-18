<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CustomerFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $phones = [];
        for ($i = 1; $i <= 25; $i++) {
            $phones[$i] = 'phone'.$i;
        }

        for ($i = 0; $i < 10; $i++) {
            $customer = $this->createCustomer($phones);

            $manager->persist($customer);
        }
        $manager->flush();
    }

    /**
     * @param array $phones
     *
     * @return Customer
     */
    private function createCustomer(array $phones) : Customer
    {
        $faker = Factory::create('fr_FR');

        $customer = new Customer();
        $customer->setFirstName($faker->firstName)
            ->setLastName($faker->lastName)
            ->setEmail($customer->getLastName().'@'.$faker->freeEmailDomain)
            ->setAddress($faker->streetAddress)
            ->setPostCode(rand(10000, 99999))
            ->setCity($faker->city)
        ;

        return $customer;
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            PhoneFixtures::class,
        ];
    }
}
