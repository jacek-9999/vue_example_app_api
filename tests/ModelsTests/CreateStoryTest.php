<?php

use App\ActionNode;
use App\ActionNodeOption;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class CreateStoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testTextToNode()
    {
        $firstNode = new ActionNode([
            'title' => 'example title',
            'description' => 'example description',
            'story_id' => 1
        ]);
        $firstNode->save();

        $savedFirstNode = ActionNode::where('id', $firstNode->id)->first();

        $title = DB::table('descriptions_pl')
            ->select('description')
            ->join(
                'action_nodes',
                'action_nodes.title_id',
                '=',
                'descriptions_pl.id')
            ->where('action_nodes.title_id', $savedFirstNode->title_id)
            ->first();
        $description = DB::table('descriptions_pl')
            ->select('description')
            ->join(
                'action_nodes',
                'action_nodes.description_id',
                '=',
                'descriptions_pl.id')
            ->where('action_nodes.description_id', $savedFirstNode->description_id)
            ->first();
        $this->assertEquals('example title', $title->description);
        $this->assertEquals('example description', $description->description);
    }

    public function testOptionsForNode()
    {
        $actionNode = new ActionNode(['story_id' => 1]);
        $actionNode->save();
        $actionNode->addOption();
        $actionNode->addOption();
        $actionNode->addOption();
        $savedOptions = ActionNodeOption::where('node_id', $actionNode->id)->get();
        foreach ($savedOptions as $el) {
            $this->assertEquals($actionNode->id, $el->node_id);
        }
        $this->assertEquals(3, count($savedOptions));
    }

    public function testFullProcess()
    {
        $initialNode = new ActionNode([
            'title' => 'initial node title',
            'description' => 'initial node description',
            'story_id' => 1
        ]);
        $initialNode->is_initial = true;
        $initialNode->save();
        $finalNode = new ActionNode([
            'title' => 'final node title',
            'description' => 'final node description',
            'story_id' => 1
            ]);
        $finalNode->is_final = true;
        $finalNode->save();

        $descriptionsText = [
            'desc first lvl A',
            'desc first lvl B',
            'desc first lvl C'];
        $titlesText = [
            'title first lvl A',
            'title first lvl B',
            'title first lvl C'
        ];

        /*
         *  Creating first level nodes (attached to initial node as option by mapping) in loop and add it to story.
         */
        $firstLevelNodes = [];
        for ($i = 0; $i < 3; $i++) {
            $currentNode = new ActionNode([
                'title' => $titlesText[$i],
                'description' => $descriptionsText[$i],
                'story_id' => 1
            ]);
            $currentNode->save();
            array_push($firstLevelNodes, $currentNode->id);
            /*
             * Map  initialNode option to current node.
             */
            $currentNode->setAsTarget($initialNode->addOption()->id);
        }

        $descriptionsTextSecondLevel = [
            'desc sec lvl A',
            'desc sec lvl B',
            'desc sec lvl C',
            'desc sec lvl D',
            'desc sec lvl E',
            'desc sec lvl F'
        ];
        $titlesTextSecondLevel = [
            'title sec lvl A',
            'title sec lvl B',
            'title sec lvl C',
            'title sec lvl D',
            'title sec lvl E',
            'title sec lvl F'
        ];
        $secondLevelNodes = [];
        $variants = ['_variant_1', '_variant_2'];
        /*
         * Generating second level of nodes. Two nodes for any node from first level.
         */
        foreach ($firstLevelNodes as $k => $firstLevelNodeID) {
            foreach ($variants as $variant) {
                $currentNode = new ActionNode([
                    'title' => $titlesTextSecondLevel[$k] . $variant,
                    'description' => $descriptionsTextSecondLevel[$k] . $variant,
                    'story_id' => 1
                ]);
                $currentNode->save();
                array_push($secondLevelNodes, $currentNode->id);
                /*
                 * New option for first level node.
                 */
                $optionId = ActionNode::where('id', $firstLevelNodeID)
                    ->first()
                    ->addOption();
                /*
                 * Map previous created option to current node.
                 */
                $currentNode->setAsTarget($optionId->id);
                // creating option and mappings to next nodes from current
                $finalNode->setAsTarget($currentNode->addOption()->id);
                $initialNode->setAsTarget($currentNode->addOption()->id);
            }
        }
        /*
         * 1 initial + 3 first level + 6 second level + 1 final = 11
         */
        $this->assertEquals(11, ActionNode::all()->count());

        /*
         * 3 options from first node +
         * 3 * 2 options for first level nodes +
         * 6 * 2 options for second level nodes =
         * 3 + 6 + 12 = 21
         */
        $this->assertEquals(21, ActionNodeOption::all()->count());

        /*
         * Test traversing nodes, intentionally without recursion for testing purposes.
         */
        foreach ($initialNode->getOptions() as $targetNodeL1) {
            $this->assertTrue(in_array(
                $targetNodeL1['description'],
                ['desc first lvl A',
                 'desc first lvl B',
                 'desc first lvl C']
            ));
            foreach (ActionNode::where('id', $targetNodeL1['id'])->first()->getOptions() as $targetNodeL2) {
                $this->assertTrue(in_array(
                    $targetNodeL2['description'],
                    ['desc sec lvl A_variant_1',
                     'desc sec lvl A_variant_2',
                     'desc sec lvl B_variant_1',
                     'desc sec lvl B_variant_2',
                     'desc sec lvl C_variant_1',
                     'desc sec lvl C_variant_2']
                ));
                foreach (ActionNode::where('id', $targetNodeL2['id'])->first()->getOptions() as $targetNodeL3) {
                    $this->assertTrue(in_array(
                        $targetNodeL3['description'],
                        ['initial node description',
                         'final node description']
                    ));
                }
            }
        }
        $stories = ActionNode::getStories();
        $this->assertEquals(1, $stories->count());
        $this->assertEquals('initial node title', $stories->first()->title);
        $this->assertEquals(
            'initial node description',
            ActionNode::where('id', $stories->first()->id)->first()->getDescription()
        );
    }
}