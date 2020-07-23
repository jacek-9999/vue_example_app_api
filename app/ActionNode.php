<?php

namespace App;

use Illuminate\Support\Facades\DB;
use App\ActionNodeOption;

class ActionNode extends BaseAction
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title_id', 'description_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function getOptions(): object
    {
        return ActionNodeOption::where('node_id', $this->id)->get();
    }

    public function getTitle(): string
    {
        return DB::table($this->textTable)
            ->select('description')
            ->where('id', $this->title_id);
    }
}
