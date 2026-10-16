{{Form::model($maintenanceRequest, array('route' => array('maintenance-request.action', $maintenanceRequest->id), 'method' => 'POST','enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{Form::label('fixed_date',__('Date'),array('class'=>'form-label'))}}
            {{Form::date('fixed_date',null,array('class'=>'form-control select2'))}}
        </div>
        <div class="form-group">
            {{Form::label('status',__('Status'),array('class'=>'form-label'))}}
            {{Form::select('status',$status,$maintenanceRequest->status,array('class'=>'form-control select2'))}}
        </div>
        <div class="form-group ">
            {{Form::label('amount',__('Amount'),array('class'=>'form-label'))}}
            {{Form::number('amount',null,array('class'=>'form-control'))}}
        </div>
        <div class="form-group">
            {{Form::label('labor_hours',__('Hours Worked'),array('class'=>'form-label'))}}
            {{Form::number('labor_hours',null,array('class'=>'form-control','step'=>'0.25','min'=>'0'))}}
        </div>
        <div class="form-group  col-md-12 col-lg-12">
            {{Form::label('after_photo',__('Completion Photo'),array('class'=>'form-label'))}}
            {{Form::file('after_photo',array('class'=>'form-control','accept'=>'.jpg,.jpeg,.png'))}}
        </div>
        <div class="form-group  col-md-12 col-lg-12">
            {{Form::label('invoice',__('Attachment'),array('class'=>'form-label'))}}
            {{Form::file('invoice',array('class'=>'form-control'))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}


