<?php

use App\ActionNode;
use App\ActionNodeOption;
use App\ActionNodeMapping;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class ApiTest extends TestCase
{
    use DatabaseMigrations;

    public function testPostNodeMethod()
    {
        $actionNodeData = json_encode([
            'title'=> 'test title request',
            'description' => 'test desc request'
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
            'description' => 'test add option desc request'
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
}