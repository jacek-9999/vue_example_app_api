<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActionNode extends BaseAction
{
    use SoftDeletes;
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
           $titleId = DB::table(self::$textTable)
            ->insertGetId(['description' => $attributes['title']]);
           unset($attributes['title']);
           $attributes['title_id'] = $titleId;
        }

        if (isset($attributes['description'])) {
           $descriptionId = DB::table(self::$textTable)
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
        return $option;
    }

    public function setAsTarget($optionId): void
    {
        $option = ActionNodeOption::where('id', '=', $optionId)->firstOrFail();
        $option->target_id = $this->id;
        $option->save();
    }

    public function getOptions(): array
    {
        /*
         * that assertion should be checked when trying to make mapping take place,
         * but now we check this after mapping is created
         */
        if ($this->is_final) {
            throw new \Exception('getting options from final node');
        }
        $ids = DB::table('action_node_options')
            ->join('action_nodes',
                'action_nodes.id',
                '=',
                'action_node_options.node_id')
            ->where('action_nodes.id', '=', $this->id)
            ->select('action_node_options.target_id')
            ->get();
        $optionNodes = [];
        foreach ($ids as $id) {
            $n = ActionNode::where('id', $id->target_id)->first();
            array_push(
                $optionNodes,[
                    'id' => $n->id,
                    'title' => $n->getTitle(),
                    'description' => $n->getDescription(),
                    'is_initial' => $n->is_initial,
                    'is_final' => $n->is_final
            ]);
        }
        return $optionNodes;
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
        $stories =  DB::table('action_nodes')
            ->join(
                self::$textTable,
                'action_nodes.title_id',
                '=',
                self::$textTable.'.id')
            ->where([
                ['action_nodes.is_initial', '=', true],
                ['action_nodes.deleted_at', '=', NULL]])
            ->select(
                'action_nodes.id',
                'action_nodes.story_id',
                self::$textTable.'.description AS title')
            ->get();
        // todo: optimize query without loop
        foreach ($stories as &$story) {
            $count = DB::select(DB::raw("SELECT story_id, COUNT(*) AS story_count FROM action_nodes WHERE story_id = $story->story_id AND deleted_at IS NULL GROUP BY story_id"));
            $story->story_count = $count[0]->story_count;
        }
        return $stories;
    }

    public static function getStoryNodes($id)
    {
        return DB::table('action_nodes')
            ->join(
                self::$textTable,
                self::$textTable.'.id',
                '=',
                'action_nodes.title_id')
            ->where([
                ['action_nodes.story_id', '=', $id],
                ['action_nodes.deleted_at', '=', null]])
            ->select(
                self::$textTable.'.description AS title',
                'action_nodes.id',
                'action_nodes.is_initial',
                'action_nodes.is_final')
            ->get();
    }

    public function updateTitle($newTitle) {
        $updated = DB::table(self::$textTable)
            ->where('id', '=', $this->title_id)
            ->update(['description' => $newTitle]);
        if (!$updated) {
           $this->title_id = DB::table(self::$textTable)
            ->insertGetId(['description' => $newTitle]);
            $this->save();
        }
    }

    public function updateDescription($newTitle) {
        $updated = DB::table(self::$textTable)
            ->where('id', '=', $this->description_id)
            ->update(['description' => $newTitle]);
        if (!$updated) {
           $this->description_id = DB::table(self::$textTable)
            ->insertGetId(['description' => $newTitle]);
           $this->save();
        }
    }

    public function unlink($targetId) {
        ActionNodeOption::where([['target_id', $targetId],['node_id', $this->id]])->delete();
        return 'unlinked';
    }
}
