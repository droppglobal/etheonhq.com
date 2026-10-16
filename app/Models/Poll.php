<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['question', 'description', 'property_id', 'closes_on', 'show_results', 'is_active', 'parent_id'];

    public function options()
    {
        return $this->hasMany(PollOption::class, 'poll_id', 'id')->orderBy('sort_order')->orderBy('id');
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class, 'poll_id', 'id');
    }

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function isOpen()
    {
        return $this->is_active && (empty($this->closes_on) || \Carbon\Carbon::parse($this->closes_on)->endOfDay()->gte(now()));
    }

    // [option_id => count]
    public function results()
    {
        return PollVote::where('poll_id', $this->id)->selectRaw('option_id, COUNT(*) as total')->groupBy('option_id')->pluck('total', 'option_id');
    }
}
