<?php

use Illuminate\Database\Seeder;
use Faker\Generator;
use Faker\Provider\Lorem;
use App\ActionNode;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = new Generator();
        $faker->addProvider(new Faker\Provider\Lorem($faker));
        $this->makeStory($faker, 1);
        $this->makeStory($faker, 2);
        $this->makeStory($faker, 3);
        $this->makeStory($faker, 4);
        $this->makeStory($faker, 5);
        $this->makeStory($faker, 6);
        $this->makeStory($faker, 7);
        $this->makeStory($faker, 8);
    }

    private function createNode(&$faker, $initial = false, $final = false, $story_id = 1)
    {
       $n = new ActionNode([
           'title' => $faker->sentence(4),
           'description' => $faker->sentence(20),
           'is_initial' => $initial,
           'is_final' => $final,
           'story_id' => $story_id
       ]);
       $n->save();
       return $n;
    }

    private function makeStory(&$faker, $story_id = 1)
    {
        $faker = new Generator();
        $faker->addProvider(new Faker\Provider\Lorem($faker));
        $nodes = ['l1' => [], 'l2' => []];
        $nodes['initial'] = $this->createNode($faker, true, false, $story_id);
        $nodes['final'] =   $this->createNode($faker, false, true, $story_id);
        $nodes['l1'][0] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l1'][1] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l1'][0]->setAsTarget($nodes['initial']->addOption($faker->sentence(3)));
        $nodes['l1'][1]->setAsTarget($nodes['initial']->addOption($faker->sentence(3)));
        $nodes['l2'][0] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l2'][1] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l2'][2] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l2'][3] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l2'][4] =   $this->createNode($faker, false, false, $story_id);
        $nodes['l2'][5] =   $this->createNode($faker, false, false, $story_id);
        foreach ($nodes['l1'] as $k => $n) {
            $x = $k ? 0 : 3;
            $nodes['l2'][$x + 0]->setAsTarget($n->addOption($faker->sentence(3)));
            $nodes['l2'][$x + 1]->setAsTarget($n->addOption($faker->sentence(3)));
            $nodes['l2'][$x + 2]->setAsTarget($n->addOption($faker->sentence(3)));
        }
        foreach ($nodes['l2'] as $k => $n) {
            $nodes['initial']->setAsTarget($n->addOption($faker->sentence(3)));
            $nodes['final']->setAsTarget($n->addOption($faker->sentence(3)));
        }
    }
}
