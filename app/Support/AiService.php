<?php

namespace App\Support;

use App\Models\Subscription;
use App\Models\User;

/**
 * Single entry point for every AI feature (assistant, triage, listing writer, "Write with AI").
 * The super admin picks the provider (OpenAI, Anthropic Claude or Google Gemini) plus an optional
 * backup; a request that fails on the active provider is retried once on the backup.
 */
class AiService
{
    const PROVIDERS = [
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic Claude',
        'gemini' => 'Google Gemini',
    ];

    const DEFAULT_MODELS = [
        'openai' => 'gpt-4o-mini',
        'anthropic' => 'claude-opus-5-5',
        'gemini' => 'gemini-2.5-flash',
    ];

    // where each provider's API key is stored (openai_secret_key predates multi-provider support)
    const KEY_SETTINGS = [
        'openai' => 'openai_secret_key',
        'anthropic' => 'anthropic_api_key',
        'gemini' => 'gemini_api_key',
    ];

    // Claude models that accept the server-side refusal fallback ("default" picks the fallback per refusal category)
    const CLAUDE_FALLBACK_MODELS = ['claude-opus-5-5', 'claude-opus-5', 'claude-fable-5-1', 'claude-sonnet-5-5'];

    // tests swap in \OpenAI\Testing\ClientFake (OpenAI) or a local base URL (Claude)
    public static $client = null;
    public static $anthropicBaseUrl = null;

    private static $config = null;

    public static function config()
    {
        if (self::$config === null) {
            $rows = \DB::table('settings')->where('parent_id', 1)
                ->whereIn('name', array_merge(array_values(self::KEY_SETTINGS), ['ai_provider', 'ai_backup_provider', 'openai_model', 'anthropic_model', 'gemini_model']))
                ->pluck('value', 'name')->all();
            self::$config = $rows;
        }
        return self::$config;
    }

    public static function flushConfig()
    {
        self::$config = null;
    }

    public static function activeProvider()
    {
        $provider = self::config()['ai_provider'] ?? 'openai';
        return array_key_exists($provider, self::PROVIDERS) ? $provider : 'openai';
    }

    public static function backupProvider()
    {
        $provider = self::config()['ai_backup_provider'] ?? '';
        return array_key_exists($provider, self::PROVIDERS) && $provider !== self::activeProvider() ? $provider : null;
    }

    public static function model($provider)
    {
        $model = trim((string) (self::config()[$provider . '_model'] ?? ''));
        return $model !== '' ? $model : self::DEFAULT_MODELS[$provider];
    }

    // API key of a provider, or null when unset (demo installs ship a masked "******" placeholder)
    public static function key($provider = null)
    {
        $provider = $provider ?: self::activeProvider();
        $key = trim((string) (self::config()[self::KEY_SETTINGS[$provider]] ?? ''));
        return ($key === '' || preg_match('/^\*+$/', $key)) ? null : $key;
    }

    // providers to try, in order: active first, then the backup (only those with a key)
    public static function chain()
    {
        $chain = [];
        foreach (array_filter([self::activeProvider(), self::backupProvider()]) as $provider) {
            if (self::key($provider) || ($provider == 'openai' && static::$client !== null)) {
                $chain[] = $provider;
            }
        }
        return $chain;
    }

    public static function enabledFor($parentId)
    {
        if (!self::chain()) {
            return false;
        }
        if ((int) $parentId === 1) {
            return true; // super admin
        }
        if (getSettingsValByIdName(1, 'pricing_feature') != 'on') {
            return true;
        }
        $owner = User::find($parentId);
        $subscription = $owner ? Subscription::find($owner->subscription) : null;
        return $subscription && $subscription->enabled_openai == 1;
    }

    /**
     * Sends a chat and returns the decoded JSON object the model was told to produce, or null on failure.
     * $messages use roles system / user / assistant. $schema (JSON Schema of the expected object) makes
     * providers that support it return exactly that shape.
     */
    public static function json(array $messages, $maxTokens = 400, array $schema = null)
    {
        foreach (self::chain() as $provider) {
            try {
                $data = self::{'ask' . ucfirst($provider)}($messages, $maxTokens, $schema);
            } catch (\Throwable $e) {
                \Log::info("AI request failed on {$provider}: " . $e->getMessage());
                $data = null;
            }
            if (is_array($data)) {
                return $data;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------ providers

    private static function decode($text)
    {
        $text = trim((string) $text);
        // tolerate a ```json fenced reply
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $text, $m)) {
            $text = $m[1];
        }
        $data = json_decode($text, true);
        return is_array($data) ? $data : null;
    }

    private static function askOpenai(array $messages, $maxTokens, $schema)
    {
        $client = static::$client ?: \OpenAI::client(self::key('openai'));
        $result = $client->chat()->create([
            'model' => self::model('openai'),
            'messages' => $messages,
            'temperature' => 0.2,
            'max_tokens' => $maxTokens,
            'response_format' => ['type' => 'json_object'],
        ]);
        return self::decode($result->choices[0]->message->content ?? '');
    }

    private static function askAnthropic(array $messages, $maxTokens, $schema)
    {
        $system = [];
        $turns = [];
        foreach ($messages as $message) {
            if (($message['role'] ?? '') === 'system') {
                $system[] = $message['content'];
            } elseif (in_array($message['role'] ?? '', ['user', 'assistant'])) {
                $turns[] = ['role' => $message['role'], 'content' => (string) $message['content']];
            }
        }

        $model = self::model('anthropic');
        $client = new \Anthropic\Client(apiKey: self::key('anthropic'), baseUrl: static::$anthropicBaseUrl);

        $outputConfig = [];
        // these short JSON tasks don't need deep reasoning; effort is accepted by current Opus/Sonnet/Fable models
        if (preg_match('/^claude-(opus|sonnet|fable)-(5|4-[678])/', $model)) {
            $outputConfig['effort'] = 'low';
        }
        if ($schema) {
            $outputConfig['format'] = ['type' => 'json_schema', 'schema' => $schema];
        }

        $params = [
            'model' => $model,
            // thinking tokens count toward max_tokens, so leave room beyond the expected answer
            'maxTokens' => max(16000, (int) $maxTokens),
            'messages' => $turns,
            'system' => implode("\n\n", $system) ?: null,
            'outputConfig' => $outputConfig ?: null,
        ];

        try {
            if (in_array($model, self::CLAUDE_FALLBACK_MODELS)) {
                // server-side fallback: a request declined for policy reasons is retried on another Claude model
                $message = $client->beta->messages->create(...$params, betas: ['server-side-fallback-2026-07-01'], fallbacks: 'default');
            } else {
                $message = $client->messages->create(...$params);
            }
        } catch (\Anthropic\Core\Exceptions\NotFoundException $e) {
            \Log::info("Claude model \"{$model}\" was not found - check the model name in AI settings.");
            return null;
        } catch (\Anthropic\Core\Exceptions\AuthenticationException $e) {
            \Log::info('Claude API key was rejected - check the key in AI settings.');
            return null;
        } catch (\Anthropic\Core\Exceptions\RateLimitException $e) {
            \Log::info('Claude rate limit reached: ' . $e->getMessage());
            return null;
        } catch (\Anthropic\Core\Exceptions\APIStatusException $e) {
            \Log::info('Claude API error (' . ($e->type?->value ?? 'unknown') . '): ' . $e->getMessage());
            return null;
        } catch (\Anthropic\Core\Exceptions\APIConnectionException $e) {
            \Log::info('Could not reach the Claude API: ' . $e->getMessage());
            return null;
        }

        if (in_array($message->stopReason, ['refusal', 'max_tokens'])) {
            \Log::info('Claude stopped without an answer: ' . $message->stopReason);
            return null;
        }
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                return self::decode($block->text);
            }
        }
        return null;
    }

    private static function askGemini(array $messages, $maxTokens, $schema)
    {
        $system = [];
        $contents = [];
        foreach ($messages as $message) {
            $role = $message['role'] ?? '';
            if ($role === 'system') {
                $system[] = $message['content'];
            } elseif (in_array($role, ['user', 'assistant'])) {
                $contents[] = ['role' => $role === 'assistant' ? 'model' : 'user', 'parts' => [['text' => (string) $message['content']]]];
            }
        }

        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => max(2048, (int) $maxTokens),
                'responseMimeType' => 'application/json',
            ],
        ];
        if ($system) {
            $body['systemInstruction'] = ['parts' => [['text' => implode("\n\n", $system)]]];
        }

        $model = self::model('gemini');
        $response = \Illuminate\Support\Facades\Http::timeout(60)
            ->withHeaders(['x-goog-api-key' => self::key('gemini')])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent', $body);

        if (!$response->successful()) {
            \Log::info('Gemini API error ' . $response->status() . ': ' . mb_substr((string) $response->json('error.message'), 0, 300));
            return null;
        }
        $candidate = $response->json('candidates.0');
        if (empty($candidate) || in_array($candidate['finishReason'] ?? '', ['SAFETY', 'MAX_TOKENS', 'RECITATION', 'PROHIBITED_CONTENT'])) {
            \Log::info('Gemini stopped without an answer: ' . ($candidate['finishReason'] ?? 'no candidate'));
            return null;
        }
        $text = '';
        foreach ($candidate['content']['parts'] ?? [] as $part) {
            $text .= $part['text'] ?? '';
        }
        return self::decode($text);
    }
}
