<?php

namespace App\Http\Controllers;
use App\ActionNode;
use App\ActionNodeOption;
use Illuminate\Http\Request;

class ActionNodeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function read($id)
    {
        $node = ActionNode::where('id', $id)
            ->firstOrFail();
        $responseData = $node->only(['id', 'is_initial', 'is_final']);
        $responseData['title'] = $node->getTitle();
        $responseData['description'] = $node->getDescription();
        if (!$node->is_final) {
            $responseData['options'] = $node->getOptions();
        }
        return response()->json($responseData);
    }

    public function readOptions($id)
    {
        $node = ActionNode::where('id', $id)
            ->firstOrFail();
        return response()->json($node->getOptions());
    }

    public function create(Request $request)
    {
        $payload = $request->toArray();
        if ((bool)$payload['is_initial'] && (bool)$payload['is_final']) {
            throw new \Exception("initial can't be final");
        }
        if ($payload['story_id'] === 'new') {
            $payload['story_id'] = ActionNode::max('story_id') + 1;
            $payload['is_initial'] = true;
        }

        $node = new ActionNode([
            'story_id' => $payload['story_id'],
            'is_initial' => $payload['is_initial'] ?? false,
            'is_final' => $payload['is_final'] ?? false,
        ]);
        $node->save();
        $node->updateTitle($payload['title']);
        $node->updateDescription($payload['description']);
        return response()->json($node->only('id'));
    }

    public function addOption(Request $request)
    {
        $baseNode = ActionNode::where('id', $request->input('base_id'))
            ->firstOrFail();
        $option = $baseNode->addOption('dsc');
        $targetNode = ActionNode::where('id', $request->input('target_id'))
            ->firstOrFail();
        $targetNode->setAsTarget($option->id);
        return response()->json(['status' => 'assigned']);
    }

    public function setTarget(Request $request)
    {
        $option = ActionNodeOption::where('id', $request->input('option_id'))
            ->firstOrFail();
        $targetNode = ActionNode::where('id', $request->input('node_id'))
            ->firstOrFail();
        $targetNode->setAsTarget($option->id);
        return response()->json(['assigned']);
    }

    public function getOptionTarget($optionId)
    {
        $targetNode = ActionNodeOption::where('id', $optionId)
            ->firstOrFail()
            ->getTargetNode();
        $responseData = $targetNode->only(['id', 'is_initial', 'is_final']);
        $responseData['title'] = $targetNode->getTitle();
        $responseData['description'] = $targetNode->getDescription();
        return response()->json($responseData);
    }


    public function edit(Request $request)
    {
        $payload = $request->toArray();
        $node = ActionNode::where('id', $payload['id'])->firstOrFail();
        if (
            $node->is_initial && (bool)$payload['is_final'] ||
            $node->is_final && (bool)$payload['is_initial']
        ) {
            throw new \Exception("initial can't be final");
        }
        $node->updateTitle($payload['title']);
        $node->updateDescription($payload['description']);
        $node->is_final = (bool)$payload['is_final'];
        $node->save();
        $responseData = $node->only(['id', 'is_initial', 'is_final']);
        $responseData['title'] = $node->getTitle();
        $responseData['description'] = $node->getDescription();
        return response()->json($responseData);
    }

    public function delete($id)
    {
        ActionNode::where('id', '=', $id)->firstOrFail()->delete();
    }

    public function storyDelete($id)
    {
        ActionNode::where('story_id', '=', $id)->delete();
    }

    public function getStoriesList()
    {
        $resp = ActionNode::getStories();
        return response()->json($resp);
    }

    public function getStory($id)
    {
        $resp = ActionNode::getStoryNodes($id);
        return response()->json($resp);
    }
}
