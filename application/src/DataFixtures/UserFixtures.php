<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array {
         return ['users'];
    }

    /**
     * Create a fixed number of users with fake names.
     */
    public function load(ObjectManager $manager) {
        for ($i = 0; $i < 20; $i++) {
            $faker = Factory::create();
            $user = new User();
            $username = $faker->colorName();
            $user->setUsername($username .'#'. time() . '.' . $i);
            $user->setDisplayName($username);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
