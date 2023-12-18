<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setEmail("user$i@gmail.com");
            $user->setPassword($this->hasher->hashPassword($user, "user"));
            $manager->persist($user);
        }

        for ($i = 0; $i < 2; $i++) {
            $admin = new User();
            $admin->setEmail("admin$i@gmail.com");
            $admin->setPassword($this->hasher->hashPassword($admin, "admin"));
            $admin->setRoles(["ROLE_ADMIN"]);
            $manager->persist($admin);
        }

        $manager->flush();
    }
    public static function getGroups(): array
    {
        return ['user'];
    }
}
