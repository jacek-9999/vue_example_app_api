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
            ->first();
        $responseData = $node->only(['id', 'is_initial', 'is_final']);
        $responseData['title'] = $node->getTitle();
        $responseData['description'] = $node->getDescription();
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
        $node = new ActionNode([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'story_id' => $request->input('story_id'),
            'is_initial' => $request->input('is_initial') ?? false,
            'is_final' => $request->input('is_final') ?? false,
        ]);
        $node->save();
        return response()->json($node->only('id'));
    }

    public function addOption(Request $request)
    {
       $node = ActionNode::where('id', $request->input('node_id'))
           ->firstOrFail();
       $optionId = $node->addOption($request->input('description'));
       return response()->json(['option_id' => $optionId]);
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
