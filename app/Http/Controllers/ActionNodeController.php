<?php

namespace App\Http\Controllers;

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

    }

    public function edit($id, Request $request)
    {

    }

    public function delete($id)
    {

    }
}
