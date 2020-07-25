<?php

namespace App;

class ActionNodeMapping extends BaseAction
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goto_id', 'option_id', 'description_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function getMappedNode()
    {
        return ActionNode::where('id', $this->goto_id)->firstOrFail();
    }
}
