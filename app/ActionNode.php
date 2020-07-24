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

    public function __construct(array $attributes = array())
    {
        if (isset($attributes['title'])) {
           $initialNodeTitleID = DB::table('descriptions_pl')
            ->insertGetId(['description' => $attributes['title']]);
           unset($attributes['title']);
           $attributes['title_id'] = $initialNodeTitleID;
        }

        if (isset($attributes['description'])) {
           $initialNodeDescriptionID = DB::table('descriptions_pl')
            ->insertGetId(['description' => $attributes['description']]);
            unset($attributes['description']);
           $attributes['description_id'] = $initialNodeDescriptionID;
        }

        parent::__construct($attributes);
    }

    public function getOptions(): object
    {
        /*
         * that assertion should be checked when trying to make mapping take place,
         * but now we check this after mapping is created
         */
        if ($this->is_final) {
            throw new \Exception('getting options from final node');
        }
        return ActionNodeOption::where('node_id', $this->id)->get();
    }

    public function getTitle(): string
    {
        $out = DB::table($this->textTable)
            ->select('description')
            ->where('id', $this->title_id)
            ->first();
        return $out->description ?? '';
    }

    public static function getStories()
    {
        return ActionNode::where('is_initial', true)->get();
    }
}
