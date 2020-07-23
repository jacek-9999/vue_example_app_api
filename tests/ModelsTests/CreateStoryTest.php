<?php

use App\Story;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreateStoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoryCreate()
    {
        $data = [
            'story_id' => 1,
        ];
        $story = new Story($data);
        $story->save();
        $this->assertEquals(1, Story::where('story_id', 1)->first()->story_id);
    }
}
