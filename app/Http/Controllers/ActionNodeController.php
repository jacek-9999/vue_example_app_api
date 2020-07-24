<?php

namespace App\Http\Controllers;
use App\ActionNode;
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
        return response()->json(['received_id' => $id]);
    }

    public function create(Request $request)
    {
        $node = new ActionNode([
            'title' => $request->input('title'),
            'description' => $request->input('description')]);
        $node->save();
        return response()->json($node->only('id'));
    }

    public function edit($id, Request $request)
    {

    }

    public function delete($id)
    {

    }
}
