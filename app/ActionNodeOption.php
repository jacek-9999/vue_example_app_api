<?php

namespace App;

class ActionNodeOption extends BaseAction
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'node_id', 'description_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function getMapping()
    {
        return ActionNodeMapping::where('option_id', $this->id)
            ->first();
    }
}
