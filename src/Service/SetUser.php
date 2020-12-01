<?php


namespace App\Service;

use App\Entity\User;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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

    /**
     * @param User $user
     *
     * @return string[]|ConstraintViolationListInterface
     */
    public function set(User $user)
    {
        if (!preg_match("#(?=.*\d)(?=.*[A-Z])(?=.*[a-z])([-+!*$@%_\w]{6,20})$#", $user->getPassword())) {
            return ["message" => "Le champs password doit comporter entre 6 et 20 caractères dont une majuscule, une minuscule et un chiffre"];
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
        $user->setRoles($user->getRoles());

        return $this->validator->validate($user);
    }
}
