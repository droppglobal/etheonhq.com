
<?php
    $aiDef = \App\Support\AiWriter::types()[$type] ?? null;
    $aiUid = 'aiw_' . $type . '_' . substr(md5(uniqid('', true)), 0, 6);
?>
<?php if($aiDef && \App\Support\AiWriter::available()): ?>
    <div class="ai-writer mb-2" id="<?php echo e($aiUid); ?>" data-type="<?php echo e($type); ?>"
        data-outputs='<?php echo json_encode(collect($aiDef['outputs'])->map(fn($o) => ['label' => __($o[0]), 'format' => $o[1]]), 512) ?>'
        data-context='<?php echo json_encode(array_keys($aiDef['context']), 15, 512) ?>'
        data-placeholders='<?php echo json_encode(array_values($placeholders ?? []), 15, 512) ?>'>
        <a class="btn btn-sm btn-light-secondary" data-bs-toggle="collapse" href="#<?php echo e($aiUid); ?>_panel" role="button"><i class="ti ti-wand"></i> <?php echo e(__('Write with AI')); ?></a>
        <div class="collapse" id="<?php echo e($aiUid); ?>_panel">
            <div class="border rounded p-3 mt-2 bg-light text-start">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small mb-0"><?php echo e(__('What should it say?')); ?></label>
                        <textarea class="form-control form-control-sm aiw-brief" rows="2" maxlength="1000" placeholder="<?php echo e(__('A few words or notes, e.g. "water supply off on Sunday 10am-2pm for tank cleaning"')); ?>"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-0"><?php echo e(__('Tone')); ?></label>
                        <?php echo e(Form::select('aiw_tone', \App\Support\AiWriter::tones(), 'professional', ['class' => 'form-control form-control-sm aiw-tone', 'id' => $aiUid . '_tone'])); ?>

                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-0"><?php echo e(__('Length')); ?></label>
                        <?php echo e(Form::select('aiw_length', \App\Support\AiWriter::lengths(), 'medium', ['class' => 'form-control form-control-sm aiw-length', 'id' => $aiUid . '_length'])); ?>

                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-0"><?php echo e(__('Language')); ?></label>
                        <input type="text" class="form-control form-control-sm aiw-language" value="<?php echo e(\Auth::user()->lang && \Auth::user()->lang != 'english' ? ucfirst(\Auth::user()->lang) : 'English'); ?>" maxlength="30">
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-secondary aiw-generate"><i class="ti ti-wand"></i> <?php echo e(__('Generate')); ?></button>
                    <span class="small text-danger aiw-error"></span>
                </div>
                <div class="aiw-result d-none mt-3">
                    <div class="aiw-preview"></div>
                    <div class="mt-2 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary aiw-use"><?php echo e(__('Use this')); ?></button>
                        <button type="button" class="btn btn-sm btn-light-secondary aiw-generate"><?php echo e(__('Try again')); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function() {
            var box = document.getElementById('<?php echo e($aiUid); ?>');
            var run = function() {
                var $box = $(box), $scope = $box.closest('form'), latest = null;
                if (!$scope.length) { $scope = $box.closest('.modal-content, .card, body'); }
                var outputs = $box.data('outputs'), contextFields = $box.data('context'), placeholders = $box.data('placeholders');
                function field(name) { return $scope.find('[name="' + name + '"]').first(); }
                function fieldValue(name) {
                    var $f = field(name);
                    if (!$f.length) return '';
                    if ($f.is('select')) { return $f.find('option:selected').text(); }
                    if ($f.is(':radio, :checkbox')) { return $scope.find('[name="' + name + '"]:checked').val() || ''; }
                    return $f.val();
                }
                function editorFor($f) {
                    var el = $f.nextAll('.ck-editor').first().find('.ck-editor__editable')[0] || $f.parent().find('.ck-editor__editable')[0];
                    return el && el.ckeditorInstance ? el.ckeditorInstance : null;
                }
                function generate() {
                    var context = {};
                    $.each(contextFields, function(_, name) { context[name] = fieldValue(name); });
                    $box.find('.aiw-error').text('');
                    $box.find('.aiw-generate').prop('disabled', true).first().html('<span class="spinner-border spinner-border-sm"></span> <?php echo e(__('Writing...')); ?>');
                    $.post('<?php echo e(route('ai.writer')); ?>', {
                        _token: '<?php echo e(csrf_token()); ?>', type: $box.data('type'), brief: $box.find('.aiw-brief').val(), context: context,
                        tone: $box.find('.aiw-tone').val(), length: $box.find('.aiw-length').val(), language: $box.find('.aiw-language').val(), placeholders: placeholders
                    }).done(function(res) {
                        latest = res.fields;
                        var $p = $box.find('.aiw-preview').empty();
                        $.each(res.fields, function(name, value) {
                            var meta = outputs[name] || { label: name, format: 'plain' };
                            var $v = $('<div class="border rounded bg-white p-2 small">');
                            if (meta.format === 'html') { $v.html(value); } else { $v.css('white-space', 'pre-line').text(value); }
                            $p.append($('<div class="fw-semibold small mt-2">').text(meta.label)).append($v);
                        });
                        $box.find('.aiw-result').removeClass('d-none');
                    }).fail(function(xhr) {
                        $box.find('.aiw-error').text(xhr.status == 429 ? '<?php echo e(__('Please wait a minute before generating again.')); ?>' : ((xhr.responseJSON && xhr.responseJSON.error) || '<?php echo e(__('Something went wrong.')); ?>'));
                    }).always(function() {
                        $box.find('.aiw-generate').prop('disabled', false).first().html('<i class="ti ti-wand"></i> <?php echo e(__('Generate')); ?>');
                    });
                }
                $box.find('.aiw-generate').on('click', generate);
                $box.find('.aiw-use').on('click', function() {
                    if (!latest) return;
                    var filled = 0;
                    $.each(latest, function(name, value) {
                        var $f = field(name);
                        if (!$f.length) return;
                        var ed = editorFor($f);
                        if (ed) { ed.setData(value); } else { $f.val((outputs[name] || {}).format === 'html' ? $('<div>').html(value).text() : value).trigger('input').trigger('change'); }
                        filled++;
                    });
                    $box.find('.collapse').collapse('hide');
                    if (!filled) { $box.find('.aiw-error').text('<?php echo e(__('Could not find the fields to fill on this form.')); ?>'); }
                });
            };
            if (window.jQuery) { run(); } else { document.addEventListener('DOMContentLoaded', run); }
        })();
    </script>
<?php endif; ?>
<?php /**PATH /home/etheonbe/public_html/resources/views/ai/writer.blade.php ENDPATH**/ ?>