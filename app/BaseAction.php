<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


abstract class BaseAction extends Model
{
    /*
     * prepared for swap languages
     */
    static $textTable = 'descriptions_pl';

    public function getDescription()
    {
        $out = DB::table(self::$textTable)
            ->select('description')
            ->where('id', $this->description_id)
            ->first();
        return $out->description ?? '';
    }

}
