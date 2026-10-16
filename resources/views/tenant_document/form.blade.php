@if (!empty($document))
    {{ Form::model($document, ['route' => ['tenant.document.update', $document->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
@else
    {{ Form::open(['route' => ['tenant.document.store', $tenant->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
            {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. Passport')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('document_type', __('Document Type'), ['class' => 'form-label']) }}
            {{ Form::select('document_type', $documentTypes, null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('expiry_date', __('Expiry Date'), ['class' => 'form-label']) }}
            {{ Form::date('expiry_date', null, ['class' => 'form-control']) }}
            <small class="text-muted">{{ __('Leave empty if it does not expire.') }}</small>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('document', __('File'), ['class' => 'form-label']) }}
            {{ Form::file('document', array_merge(['class' => 'form-control', 'accept' => '.jpg,.jpeg,.png,.pdf'], empty($document) ? ['required' => 'required'] : [])) }}
            @if (!empty($document))
                <small class="text-muted">{{ __('Leave empty to keep the current file.') }}</small>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(!empty($document) ? __('Update') : __('Upload'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
