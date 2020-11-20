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
        for ($i = 0; $i <= 30; $i++) {
            $faker = Factory::create('fr_FR');

            $customer = new Customer();
            $customer->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($customer->getLastName().'@'.$faker->freeEmailDomain)
                ->setAddress($faker->streetAddress)
                ->setPostCode(rand(10000, 99999))
                ->setCity($faker->city)
                ->getUsers($this->getReference('client-'.rand(1, 2)))
            ;

            $this->addReference('customer-'.$i, $customer);

            $manager->persist($customer);
        }
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
