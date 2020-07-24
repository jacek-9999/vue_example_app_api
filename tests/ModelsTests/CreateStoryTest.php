<?php

use App\ActionNode;
use App\ActionNodeOption;
use App\ActionNodeMapping;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class CreateStoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testTextToNode()
    {
        $q = "insert into descriptions_pl (description) VALUE (?)";
        DB::insert($q, ['example description']);
        DB::insert($q, ['example title']);
        $descriptions = DB::select("select * from descriptions_pl");

        $firstNode = new ActionNode();
        $firstNode->description_id = $descriptions[0]->id;
        $firstNode->title_id = $descriptions[1]->id;
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
        $this->assertEquals($descriptions[0]->id, $savedFirstNode->description_id);
        $this->assertEquals($descriptions[1]->id, $savedFirstNode->title_id);
    }

    public function testOptionsForNode()
    {
        $actionNode = new ActionNode();
        $actionNode->save();
        $option1 = new ActionNodeOption(['node_id' => $actionNode->id]);
        $option2 = new ActionNodeOption(['node_id' => $actionNode->id]);
        $option3 = new ActionNodeOption(['node_id' => $actionNode->id]);
        $option1->save();
        $option2->save();
        $option3->save();
        $savedOptions = ActionNodeOption::where('node_id', $actionNode->id)->get();
        foreach ($savedOptions as $el) {
            $this->assertEquals($actionNode->id, $el->node_id);
        }
        $this->assertEquals(3, count($savedOptions));
    }

    public function testOptionsNodeMappings()
    {
        $baseNode = new ActionNode();
        $baseNode->save();
        $targetNode = new ActionNode();
        $targetNode->save();
        $option1 = new ActionNodeOption(['node_id' => $baseNode->id]);
        $option1->save();
        $mapping = new ActionNodeMapping([
            'goto_id' => $targetNode->id,
            'option_id' => $option1->id
        ]);
        $mapping->save();

        $savedMapping = ActionNodeMapping::where('id', $mapping->id)->first();
        $this->assertEquals($targetNode->id, $savedMapping->goto_id);
        $this->assertEquals($option1->id, $savedMapping->option_id);
    }

    public function testFullProcess()
    {
        $initialNode = new ActionNode([
            'title' => 'initial node title',
            'description' => 'initial node description']);
        $initialNode->is_initial = true;
        $initialNode->save();
        $finalNode = new ActionNode([
            'title' => 'final node title',
            'description' => 'final node description']);
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
            $currentNode = new ActionNode();
            $createdDescId = DB::table('descriptions_pl')
                ->insertGetId(['description' => $descriptionsText[$i]]);
            $currentNode->description_id = $createdDescId;
            $createdTitleId = DB::table('descriptions_pl')
                ->insertGetId(['description' => $titlesText[$i]]);
            $currentNode->title_id = $createdTitleId;
            $currentNode->save();
            array_push($firstLevelNodes, $currentNode->id);
            /*
             * New option for initial node.
             */
            $option = new ActionNodeOption(['node_id' => $initialNode->id]);
            $option->save();

            /*
             * Map previous created option to current node.
             */
            $mapping = new ActionNodeMapping([
                'goto_id' => $currentNode->id,
                'option_id' => $option->id
            ]);
            $mapping->save();
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
                $currentNode = new ActionNode();
                $createdDescId = DB::table('descriptions_pl')
                    ->insertGetId(['description' => $descriptionsTextSecondLevel[$k] . $variant]);
                $currentNode->description_id = $createdDescId;
                $createdTitleId = DB::table('descriptions_pl')
                    ->insertGetId(['description' => $titlesTextSecondLevel[$k] . $variant]);
                $currentNode->title_id = $createdTitleId;
                $currentNode->save();
                array_push($secondLevelNodes, $currentNode->id);
                /*
                 * New option for first level node.
                 */
                $option = new ActionNodeOption(['node_id' => $firstLevelNodeID]);
                $option->save();

                /*
                 * Map previous created option to current node.
                 */
                $mapping = new ActionNodeMapping([
                    'goto_id' => $currentNode->id,
                    'option_id' => $option->id
                ]);
                $mapping->save();
                // creating option and mappings to next nodes from current
                $optionA = new ActionNodeOption(['node_id' => $currentNode->id]);
                $optionA->save();
                $mappingA = new ActionNodeMapping([
                    'goto_id' => $finalNode->id,
                    'option_id' => $optionA->id
                ]);
                $mappingA->save();
                $optionB = new ActionNodeOption(['node_id' => $currentNode->id]);
                $optionB->save();
                $mappingB = new ActionNodeMapping([
                    'goto_id' => $initialNode->id,
                    'option_id' => $optionB->id
                ]);
                $mappingB->save();
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
         * Number of options is equal to number of mappings.
         */
        $this->assertEquals(ActionNodeOption::all()->count(), ActionNodeMapping::all()->count());

        /*
         * Test traversing nodes, intentionally without recursion for testing purposes.
         */
        foreach ($initialNode->getOptions() as $optionL1) {
            $this->assertTrue(in_array($optionL1->id, [1, 2, 3]));
            $mappingL1 = $optionL1->getMapping();
            $this->assertTrue(in_array($mappingL1->goto_id, [3,4,5]));
            $mappedNodeL1 = $mappingL1->getMappedNode();
            $this->assertTrue(in_array(
                $mappedNodeL1->getDescription(),
                ['desc first lvl A',
                 'desc first lvl B',
                 'desc first lvl C']
            ));
            foreach ($mappedNodeL1->getOptions() as $optionL2) {
                $this->assertTrue(in_array($optionL2->id, [4,7,10,13,16,19]));
                $mappingL2 = $optionL2->getMapping();
                $this->assertTrue(in_array($mappingL2->goto_id, [6,7,8,9,10,11]));
                $mappedNodeL2 = $mappingL2->getMappedNode();
                $this->assertTrue(in_array(
                    $mappedNodeL2->getDescription(),
                    ['desc sec lvl A_variant_1',
                     'desc sec lvl A_variant_2',
                     'desc sec lvl B_variant_1',
                     'desc sec lvl B_variant_2',
                     'desc sec lvl C_variant_1',
                     'desc sec lvl C_variant_2']
                ));
                foreach ($mappedNodeL2->getOptions() as $optionL3) {
                    $this->assertTrue(in_array($optionL3->id, [5,6,8,9,11,12,14,15,17,18,20,21]));
                    $mappingL3 = $optionL3->getMapping();
                    /*
                     * Last level nodes in this test are mapped to final or first node.
                     * So there are only two options here.
                     */
                    $this->assertTrue(in_array($mappingL3->goto_id, [1,2]));
                    $mappedNodeL3 = $mappingL3->getMappedNode();
                    $this->assertTrue(in_array(
                        $mappedNodeL3->getDescription(),
                        ['initial node description',
                         'final node description']
                    ));
                }
            }
        }
        $stories = ActionNode::getStories();
        $this->assertEquals(1, $stories->count());
        $this->assertEquals('initial node title', $stories->first()->getTitle());
        $this->assertEquals('initial node description', $stories->first()->getDescription());
        $this->assertTrue(true);
    }
}