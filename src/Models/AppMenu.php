<?php

namespace Qihucms\App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMenu extends Model
{
    protected $fillable = [
        'title', 'line', 'config'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'config' => 'json',
    ];

    public function getConfigAttribute($value)
    {
        return array_values(json_decode($value, true) ?: []);
    }

    public function setConfigAttribute($value)
    {
        $this->attributes['config'] = json_encode(array_values($value));
    }
}
