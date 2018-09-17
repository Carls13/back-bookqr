<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Book;
use Faker\Factory;

class BooksFixture extends Fixture
{
    protected $NUM_RECORDS = 100;

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < $this->NUM_RECORDS; $i++) {
            $book = new Book();
            $book->setName($faker->words(3, true));
            $book->setXXXXX($faker->text());

            $manager->persist($book);
        }

        $manager->flush();
    }

//    /**
//     * If fixtures to be loaded before this one, it needs to "implements DependentFixtureInterface" and uncomment this
//     * @return array
//     */
//    public function getDependencies()
//    {
//        return array(
//            DummyFixture::class,
//        );
//    }
}
