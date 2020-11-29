<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PhoneFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $brandModel = ['Samsung' => 'Galaxy ', 'Apple' => 'IPhone ', 'Huawei' => 'P', 'Xiaomi' => 'Mi', 'Nokia'=> ' ' ];
        $j = 0;

        foreach ($brandModel as $brand => $model) {
            for ($i = 1; $i <= rand(5, 9) ; $i++) {
                $phone = new Phone();
                $phone->setBrand($brand)
                    ->setModel($model.$i.(($brand == 'Huawei') ? '0' : ''))
                    ->setPrice(($i+rand(5, 6))*100 + [49, 99][rand(0, 1)])
                    ->setDescription($faker->paragraph($nbSentences = 3, $variableNbSentences = true))
                    ->addCustomer($this->getReference('customer-'.rand(1, 30)))
                ;

                $this->setReference('phone'.($j+$i), $phone);

                $manager->persist($phone);
            }
            $j += 5;
        }
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return [
            CustomerFixtures::class,
        ];
    }
}
