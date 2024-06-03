<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use DateTimeImmutable;
use App\Entity\User as EntityUser;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
//use Symfony\Component\Validator\Constraints\DateTime;

class User extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 6; $i++) { 
           
            $user = new EntityUser();
            $user->setFirstname("User_".rand(0,999));
            $user->setLastname("User_".rand(0,999));
            $user->setEmail("User_".rand(0,999)."@gmail.com");
            $user->setIdUser("User_".rand(0,999));
            $user->setSexe("Non Précisé");
            $user->setCreateAt(new DateTimeImmutable());
            $user->setUpdateAt(new DateTimeImmutable()); 
            $user->setBirthday(new DateTimeImmutable()); 
            $user->setPassword("$2y$".rand(0,999999999999999999));
            $manager->persist($user);
        }
        $manager->flush();
    }
}
