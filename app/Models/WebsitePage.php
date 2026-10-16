<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// a page of an owner's public website (About, Services, Policies...), shown at /web/{code}/page/{slug}
class WebsitePage extends Model
{
    protected $fillable = ['parent_id', 'title', 'slug', 'content', 'translations', 'show_in_menu', 'is_active', 'sort_order', 'meta_title', 'meta_description'];

    // title and content in the visitor's language, falling back to the default text
    public function localized($lang)
    {
        $t = json_decode((string) $this->translations, true) ?: [];
        $own = $lang ? ($t[$lang] ?? []) : [];
        return [
            'title' => trim((string) ($own['title'] ?? '')) !== '' ? $own['title'] : $this->title,
            'content' => trim(strip_tags((string) ($own['content'] ?? ''))) !== '' ? $own['content'] : $this->content,
        ];
    }
}
