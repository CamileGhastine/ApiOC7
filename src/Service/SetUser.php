<?php


namespace App\Service;

use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetUser
{
    private $serializer;
    private $validator;
    private $passwordEncoder;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
    }
    public function set(User $user)
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
        $user->setRoles($user->getRoles());

        return $this->validator->validate($user);
    }
}