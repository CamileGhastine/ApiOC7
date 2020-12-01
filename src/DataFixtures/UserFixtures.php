<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i=1; $i<=2;$i++) {
            $user = new user();

            $user->setUsername('Client-'.$i)
                ->setPassword($this->passwordEncoder->encodePassword($user, 'pass-'.$i))
                ->setRoles($user->getRoles());

            $this->addReference('client-'.$i, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
