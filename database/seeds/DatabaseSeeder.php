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
        $nodes = ['l1' => [], 'l2' => []];
        $nodes['initial'] = $this->createNode($faker, true, false);
        $nodes['final'] =   $this->createNode($faker, false, true);
        $nodes['l1'][0] =   $this->createNode($faker);
        $nodes['l1'][1] =   $this->createNode($faker);
        $nodes['l1'][0]->setAsTarget($nodes['initial']->addOption($faker->sentence(3)));
        $nodes['l1'][1]->setAsTarget($nodes['initial']->addOption($faker->sentence(3)));
        $nodes['l2'][0] =   $this->createNode($faker);
        $nodes['l2'][1] =   $this->createNode($faker);
        $nodes['l2'][2] =   $this->createNode($faker);
        $nodes['l2'][3] =   $this->createNode($faker);
        $nodes['l2'][4] =   $this->createNode($faker);
        $nodes['l2'][5] =   $this->createNode($faker);
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

    private function createNode(&$faker, $initial = false, $final = false)
    {
       $n = new ActionNode([
           'title' => $faker->sentence(4),
           'description' => $faker->sentence(20),
           'is_initial' => $initial,
           'is_final' => $final
       ]);
       $n->save();
       return $n;
    }
}
