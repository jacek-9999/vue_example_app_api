<?php

use App\ActionNode;
use App\ActionNodeOption;
use App\ActionNodeMapping;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

class ApiTest extends TestCase
{
    use DatabaseMigrations;

    public function testApiGetNodeMethod()
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
        // @todo: test returns with linked text and options
        $this->assertEquals('{"id":1}', $response->content());
        $response = $this->call('GET', 'node/1', ['CONTENT_TYPE' => 'application/json']);
        $this->assertEquals('{"received_id":"1"}', $response->content());
    }
}