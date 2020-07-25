<?php

namespace App;

use Illuminate\Support\Facades\DB;

class ActionNode extends BaseAction
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title_id', 'description_id', 'story_id', 'is_initial', 'is_final'
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
        $option = new ActionNodeOption([
            'node_id' => $this->id,
            'description' => $description
        ]);
        $option->save();
        return $option->id;
    }

    public function setAsTarget($optionId): void
    {
        $option = ActionNodeOption::where('id', '=', $optionId)->firstOrFail();
        $option->target_id = $this->id;
        $option->save();
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
        return DB::table('action_node_options')
            ->join(
                self::$textTable,
                'action_node_options.description_id',
                '=',
                self::$textTable.'.id')
            ->join('action_nodes',
                'action_nodes.id',
                '=',
                'action_node_options.node_id')
            ->where('action_nodes.id', '=', $this->id)
            ->select(
                'action_node_options.id',
                self::$textTable.'.description')
            ->get();
    }

    public function getTitle(): string
    {
        $out = DB::table(self::$textTable)
            ->select('description')
            ->where('id', $this->title_id)
            ->first();
        return $out->description ?? '';
    }

    public static function getStories()
    {
        return ActionNode::where('is_initial', true)->get('story_id');
    }

    public static function getStoryNodes($id)
    {
        return DB::table('action_nodes')
            ->join(
                self::$textTable,
                self::$textTable.'.id',
                '=',
                'action_nodes.title_id')
            ->where('action_nodes.story_id', '=', $id)
            ->select(
                self::$textTable.'.description',
                'action_nodes.id',
                'action_nodes.is_initial',
                'action_nodes.is_final')
            ->get();
//        return ActionNode::where('story_id', $id)
//            ->get(['id', 'is_initial', 'is_final']);
    }
}
