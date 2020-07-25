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
        $nodes['initial'] = $this
            ->createNode($faker, true, false);
        $nodes['final'] =   $this
            ->createNode($faker, false, true);
        $nodes['l1'][0] =   $this
            ->createNode($faker);
        $nodes['l1'][1] =   $this
            ->createNode($faker);
        $nodes['l1'][0]->setAsTarget($nodes['initial']->addOption($faker->sentence(3)));
        $nodes['l1'][1]->setAsTarget($nodes['initial']->addOption($faker->sentence(3)));
        $nodes['l2'][0] =   $this
            ->createNode($faker);
        $nodes['l2'][1] =   $this
            ->createNode($faker);
        $nodes['l2'][2] =   $this
            ->createNode($faker);
        $nodes['l2'][3] =   $this
            ->createNode($faker);
        $nodes['l2'][4] =   $this
            ->createNode($faker);
        $nodes['l2'][5] =   $this
            ->createNode($faker);
        $l1Options = [];
        foreach ($nodes['l1'] as $k => $n) {
            $l1Options[$k] = [];
            array_push($l1Options[$k], $n->addOption($faker->sentence(3)));
            array_push($l1Options[$k], $n->addOption($faker->sentence(3)));
            array_push($l1Options[$k], $n->addOption($faker->sentence(3)));
        }
        $l2Options = [];
        foreach ($nodes['l2'] as $k => $n) {
            foreach ($l1Options as $prevNodeIndex) {
                foreach ($prevNodeIndex as $optionId) {
                    $n->setAsTarget($optionId);
                }
            }
            $l2Options[$k] = [];
            array_push($l2Options[$k], $n->addOption($faker->sentence(3)));
            array_push($l2Options[$k], $n->addOption($faker->sentence(3)));
        }
        foreach ($l2Options as $nodeOptions) {
            foreach ($nodeOptions as $option) {
                if ($option % 2 === 0) {
                    $nodes['initial']->setAsTarget($option);
                } else {
                    $nodes['final']->setAsTarget($option);
                }
            }
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
