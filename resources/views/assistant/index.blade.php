@extends('layouts.app')

@section('page-title')
    {{ __('Assistant') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Assistant') }}</li>
@endsection

@push('css-page')
    <style>
        .chat-box { height: 55vh; overflow-y: auto; background: #f8f9fb; border-radius: 10px; padding: 16px; }
        .chat-msg { width: fit-content; max-width: 80%; padding: 10px 14px; border-radius: 14px; margin-bottom: 10px; white-space: pre-line; }
        .chat-msg.user { background: var(--bs-secondary, #2ca58d); color: #fff; margin-left: auto; border-bottom-right-radius: 4px; }
        .chat-msg.assistant { background: #fff; border: 1px solid #e6e8ee; border-bottom-left-radius: 4px; }
        .chat-msg.staff { background: #fff8e6; border: 1px solid #f3dfa8; border-bottom-left-radius: 4px; }
        .chat-msg .chat-who { display: block; font-size: 11px; font-weight: 600; color: #b07d10; margin-bottom: 2px; }
        .chat-chips .btn { margin: 0 6px 6px 0; }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="ti ti-messages"></i> {{ __('Ask about your tenancy') }}</h5>
                        <small class="text-muted">{{ $aiEnabled ? __('Answers come from your own account details.') : __('Quick answers about your balance, lease and repairs.') }}</small>
                    </div>
                    <a href="{{ route('assistant.clear') }}" class="btn btn-light-secondary btn-sm">{{ __('New chat') }}</a>
                </div>
                <div class="card-body">
                    <div class="chat-box" id="chat-box">
                        <div class="chat-msg assistant">{{ __('Hi :name! How can I help? You can ask about your balance, lease or repairs, or describe a problem in your home and I can raise a maintenance request.', ['name' => \Auth::user()->first_name]) }}</div>
                        @foreach ($history as $turn)
                            <div class="chat-msg {{ $turn->role }}">@if ($turn->role == 'staff')<span class="chat-who">{{ __('Property manager') }}</span>@endif{{ $turn->content }}</div>
                        @endforeach
                    </div>
                    <div class="chat-chips mt-3">
                        @foreach ([__('What do I owe?'), __('When does my lease end?'), __('Status of my repairs'), __('How do I contact the office?')] as $chip)
                            <button type="button" class="btn btn-light-secondary btn-sm chat-chip">{{ $chip }}</button>
                        @endforeach
                    </div>
                    <form id="chat-form" class="d-flex gap-2 mt-2">
                        <input type="text" id="chat-input" class="form-control" maxlength="1000" placeholder="{{ __('Type your question...') }}" autocomplete="off">
                        <button type="submit" class="btn btn-secondary" id="chat-send"><i class="ti ti-send"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        (function() {
            var $box = $('#chat-box'), $input = $('#chat-input'), $send = $('#chat-send'), lastId = {{ (int) $lastId }};
            function add(role, text) {
                var $m = $('<div class="chat-msg">').addClass(role).text(text);
                if (role == 'staff') $m.prepend($('<span class="chat-who">').text(@json(__('Property manager'))));
                $box.append($m).scrollTop($box[0].scrollHeight);
                return $m;
            }
            function offerRequest(description) {
                var $m = add('assistant', '{{ __('Shall I raise a maintenance request for this?') }}\n"' + description + '"');
                var $btn = $('<button class="btn btn-sm btn-secondary mt-2 d-block">{{ __('Yes, raise request') }}</button>');
                $m.append($btn);
                $btn.on('click', function() {
                    $btn.prop('disabled', true);
                    $.post('{{ route('assistant.request') }}', { _token: '{{ csrf_token() }}', description: description })
                        .done(function(res) { add('assistant', res.reply); $btn.remove(); })
                        .fail(function(xhr) { add('assistant', (xhr.responseJSON && xhr.responseJSON.error) || '{{ __('Something went wrong.') }}'); $btn.prop('disabled', false); });
                });
            }
            function send(text) {
                text = $.trim(text);
                if (!text) return;
                add('user', text);
                $input.val(''); $send.prop('disabled', true);
                var $typing = add('assistant', '...');
                $.post('{{ route('assistant.message') }}', { _token: '{{ csrf_token() }}', message: text })
                    .done(function(res) {
                        $typing.remove();
                        add('assistant', res.reply);
                        if (res.last_id) lastId = Math.max(lastId, res.last_id);
                        if (res.suggest_request) offerRequest(res.suggest_request);
                    })
                    .fail(function(xhr) {
                        $typing.remove();
                        add('assistant', xhr.status == 429 ? '{{ __('You are sending messages too fast. Please wait a minute.') }}' : ((xhr.responseJSON && xhr.responseJSON.error) || '{{ __('Something went wrong.') }}'));
                    })
                    .always(function() { $send.prop('disabled', false); $input.focus(); });
            }
            $('#chat-form').on('submit', function(e) { e.preventDefault(); send($input.val()); });
            $('.chat-chip').on('click', function() { send($(this).text()); });
            $box.scrollTop($box[0].scrollHeight);

            // replies typed by the office appear without reloading
            setInterval(function() {
                if (document.hidden) return;
                $.getJSON('{{ route('assistant.updates') }}', { after: lastId }).done(function(res) {
                    (res.messages || []).forEach(function(m) { add('staff', m.content); lastId = Math.max(lastId, m.id); });
                });
            }, 20000);
        })();
    </script>
@endpush
