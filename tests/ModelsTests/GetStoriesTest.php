<?php

use App\ActionNode;
use App\ActionNodeOption;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class GetStoriesTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetStoryNodes()
    {
        $firstNode = new ActionNode([
            'title' => 'initial node title',
            'description' => 'initial node description',
            'story_id' => 1,
            'is_initial' => true
        ]);
        $firstNode->save();
        for ($i = 0; $i < 3; $i++) {
             $currentNode = new ActionNode([
                 'title' => 'title:' . $i,
                 'description' => 'desc:' . $i,
                 'story_id' => 1
             ]);
             $currentNode->save();
             $currentNode->setAsTarget($firstNode->addOption()->id);
        }
        $validOutput = '[{"id":1,"story_id":1,"title":"initial node title","story_count":4,"nodes":{"1":{"id":1,"is_initial":1,"is_final":0,"title":"initial node title","description":"initial node description"},"2":{"id":2,"is_initial":0,"is_final":0,"title":"title:0","description":"desc:0"},"3":{"id":3,"is_initial":0,"is_final":0,"title":"title:1","description":"desc:1"},"4":{"id":4,"is_initial":0,"is_final":0,"title":"title:2","description":"desc:2"}}}]';
        $this->assertEquals($validOutput, json_encode(ActionNode::getStories()->toArray()));
        $this->assertEquals(4, count(ActionNode::getStoryNodes(1)));
    }
}