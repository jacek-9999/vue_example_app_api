<?php

use App\ActionNode;
use App\ActionNodeOption;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class GetStoryNodesTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetStoryNodes()
    {
        $firstNode = new ActionNode([
            'title' => 'initial node title',
            'description' => 'initial node description',
            'story_id' => 1
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
        $this->assertEquals(4, ActionNode::getStoryNodes(1)->count());
    }
}