<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'value',
        'type',
        'parent_id',

    ];



    public static function updateSettings($key, $value)
    {
        $setting = Setting::where('name', $key)
            ->where('parent_id', 1)
            ->first();

        if ($setting) {
            $setting->value = $value;
            return $setting->save();
        } else {
            return Setting::create([
                'name' => $key,
                'value' => $value,
                'parent_id' => 1,
            ]);
        }
    }
}
