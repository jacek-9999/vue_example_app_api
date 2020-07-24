<?php

namespace App;

use Illuminate\Support\Facades\DB;

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

    public function __construct(array $attributes = [])
    {
        if (isset($attributes['description'])) {
           $descriptionId = DB::table('descriptions_pl')
            ->insertGetId(['description' => $attributes['description']]);
            unset($attributes['description']);
           $attributes['description_id'] = $descriptionId;
        }
        parent::__construct($attributes);
    }

    public function getMapping()
    {
        return ActionNodeMapping::where('option_id', $this->id)
            ->first();
    }
}
