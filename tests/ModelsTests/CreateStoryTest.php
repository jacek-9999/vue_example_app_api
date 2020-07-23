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
}
