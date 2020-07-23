<?php

use App\Story;
use App\ActionNode;
use App\ActionNodeOption;
use App\ActionNodeMapping;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class CreateStoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoryCreate()
    {
        $storyId = 1;
        DB::insert("insert into stories (story_id) VALUE (?)", [$storyId]);
        $this->assertEquals(1, Story::where('story_id', 1)->first()->story_id);
    }

    public function testAddNodesToStory()
    {
        $firstNode = new ActionNode();
        $firstNode->save();
        $secondNode = new ActionNode();
        $secondNode->save();
        $thirdNode = new ActionNode();
        $thirdNode->save();
        /*
         * Story will be handled by custom service instead of Eloquent.
         * Id will be added by this service.
         */
        $storyId = 2;
        $q = "insert into stories (story_id, node_id) VALUE ($storyId,?), ($storyId,?), ($storyId,?)";
        DB::insert(
            $q,
            [
                $firstNode->id,
                $secondNode->id,
                $thirdNode->id
            ]
        );
        $result = DB::select("select id, story_id, node_id from stories");

        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals(1, $result[0]->node_id);
        $this->assertEquals(2, $result[1]->id);
        $this->assertEquals(2, $result[1]->node_id);
        $this->assertEquals(3, $result[2]->id);
        $this->assertEquals(3, $result[2]->node_id);
        /*
         * story is composed from 3 nodes, all should have same story_id
         */
        $this->assertEquals(
            [2],
            array_unique([$result[0]->story_id, $result[1]->story_id, $result[2]->story_id])
        );
    }

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
        $storyId = 2;
        $storiesInsert = "insert into stories (story_id, node_id) VALUE ($storyId,?)";
        $initialNodeTitleID = DB::table('descriptions_pl')
            ->insertGetId(['description' => 'initial node title']);
        $initialNodeDescID = DB::table('descriptions_pl')
            ->insertGetId(['description' => 'initial node description']);
        $finalNodeTitleID = DB::table('descriptions_pl')
            ->insertGetId(['description' => 'final node title']);
        $finalNodeDescID = DB::table('descriptions_pl')
            ->insertGetId(['description' => 'final node description']);

        $initialNode = new ActionNode();
        $initialNode->title_id = $initialNodeTitleID;
        $initialNode->description_id = $initialNodeDescID;
        $initialNode->save();
        DB::insert($storiesInsert, [$initialNode->id,]);
        $finalNode = new ActionNode();
        $finalNode->title_id = $finalNodeTitleID;
        $finalNode->description_id = $finalNodeDescID;
        $finalNode->save();
        DB::insert($storiesInsert, [$finalNode->id,]);

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

            DB::insert($storiesInsert, [$currentNode->id,]);
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

                DB::insert($storiesInsert, [$currentNode->id,]);
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
    }
}