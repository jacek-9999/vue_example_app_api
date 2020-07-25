<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class ApiTest extends TestCase
{
    use DatabaseMigrations;

    public function testPostNodeMethod()
    {
        $actionNodeData = json_encode([
            'title'=> 'test title request',
            'description' => 'test desc request',
            'story_id' => 1
        ]);
        $response = $this
            ->call(
                'POST',
                'node', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $actionNodeData
            );
        $this->assertEquals('{"id":1}', $response->content());
        $response = $this->call('GET', 'node/1', ['CONTENT_TYPE' => 'application/json']);
        $this->assertEquals(
            '{"id":1,"is_initial":0,"is_final":0,"title":"test title request","description":"test desc request"}',
            $response->content()
        );
    }

    public function testAddOptionToNodeMethod()
    {
        // create node first
        $actionNodeData = json_encode([
            'title'=> 'test add option title request',
            'description' => 'test add option desc request',
            'story_id' => 1
        ]);
        $response = $this
            ->call(
                'POST',
                'node', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $actionNodeData
            );
        $this->assertEquals('{"id":1}', $response->content());
        $addOptionData = json_encode([
            'node_id' => 1,
            'description' => 'option description'
        ]);
        $response = $this
            ->call(
                'POST',
                'option', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $addOptionData
            );
        $this->assertEquals('{"option_id":1}', $response->content());
    }

    public function testAssignTargetToOption()
    {
        $firstNodeData = json_encode([
            'title'=> 'first node title',
            'description' => 'first node descritpion',
            'story_id' => 1
        ]);
        $secondNodeData = json_encode([
            'title'=> 'second node title',
            'description' => 'second node descritpion',
            'story_id' => 1
        ]);
        $this->call(
                'POST',
                'node', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $firstNodeData
            );
        $this->call(
                'POST',
                'node', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $secondNodeData
            );
        $firstNodeOption = json_encode([
            'node_id' => 1,
            'description' => 'option description'
        ]);
        $this->call(
                'POST',
                'option', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $firstNodeOption
            );
        $assignData = json_encode([
            'option_id' => 1,
            'node_id' => 2 // second created node
        ]);
        $response = $this->call(
                'POST',
                'target', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $assignData
        );
        $firstNodeOptions =
            $this->call(
                    'GET',
                    'node_options/1',
                    ['CONTENT_TYPE' => 'application/json']
                );
        $this->assertEquals('[{"id":1,"description":"option description"}]',
            $firstNodeOptions->content());

        $this->assertEquals('["assigned"]', $response->content());
        $validTarget = '{"id":2,"is_initial":0,"is_final":0,"title":"second node title","description":"second node descritpion"}';

        $targetMappedByOption = $this->call(
                    'GET',
                    'target/1', // 1 is option ID
                    ['CONTENT_TYPE' => 'application/json']);
        $this->assertEquals($validTarget, $targetMappedByOption->content());
    }

    public function testGetStory()
    {
        $stories = ['A' => [], 'B' => []];
        $stories['A'][0] = json_encode([
            'title' => 'first node title',
            'description' => 'first node descritpion',
            'story_id' => 1,
            'is_initial' => true
        ]);
        $stories['A'][1] = json_encode([
            'title' => 'second node title',
            'description' => 'second node descritpion',
            'story_id' => 1
        ]);
        $stories['A'][2] = json_encode([
             'title' => 'third node title',
             'description' => 'third node descritpion',
             'story_id' => 1
        ]);
        $stories['B'][0] = json_encode([
            'title' => 'first node title',
            'description' => 'first node descritpion',
            'story_id' => 2,
            'is_initial' => true
        ]);
        $stories['B'][1] = json_encode([
            'title' => 'second node title',
            'description' => 'second node descritpion',
            'story_id' => 2
        ]);
        $stories['B'][2] = json_encode([
             'title' => 'third node title',
             'description' => 'third node descritpion',
             'story_id' => 2
        ]);
        foreach ($stories as $story) {
            foreach ($story as $node) {
                $this->call(
                'POST', 'node', [], [], [],
                ['CONTENT_TYPE' => 'application/json'], $node);
            }
        }

        $resp = $this->call(
            'GET', 'stories',
            ['CONTENT_TYPE' => 'application/json']
        )->content();
        $this->assertEquals('[{"story_id":1},{"story_id":2}]', $resp);
    }
}