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

    public function __construct(array $attributes = [])
    {
        if (isset($attributes['title'])) {
           $titleId = DB::table('descriptions_pl')
            ->insertGetId(['description' => $attributes['title']]);
           unset($attributes['title']);
           $attributes['title_id'] = $titleId;
        }

        if (isset($attributes['description'])) {
           $descriptionId = DB::table('descriptions_pl')
            ->insertGetId(['description' => $attributes['description']]);
            unset($attributes['description']);
           $attributes['description_id'] = $descriptionId;
        }

        parent::__construct($attributes);
    }

    public function addOption(string $description = '')
    {
        $option = new ActionNodeOption(['node_id' => $this->id]);
        $option->save();
        return $option->id;
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
