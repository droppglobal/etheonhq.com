<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Frontend Settings')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Frontend Settings')); ?></li>
<?php $__env->stopSection(); ?>
<?php
    $profile = asset(Storage::url('upload/profile'));
    $settings = settings();
    $activeTab = session('tab', 'profile_tab_' . ($additionals->first()->id ?? 0));
?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $('.location').on('click', '.location_list_remove', function() {
            if ($('.location_list').length > 1) {
                $(this).closest('.location_remove').remove();
            }
        });
        $('.location').on('click', '.location_clone', function() {
            var clonedlocation = $(this).closest('.location').find('.location_list').first().clone();
            clonedlocation.find('input[type="text"]').val('');
            $(this).closest('.location').find('.location_list_results').append(clonedlocation);
        });
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row setting_page_cnt">
                        <div class="col-lg-4">
                            <ul class="nav flex-column nav-tabs account-tabs mb-3" id="myTab" role="tablist">
                                <?php $__currentLoopData = $additionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section_key => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $section->content_value = !empty($section->content_value)
                                            ? json_decode($section->content_value, true)
                                            : [];
                                    ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo e(empty($activeTab) || $activeTab == 'profile_tab_' . $section->id ? ' active ' : ''); ?>"
                                            id="profile-tab-<?php echo e($section->id); ?>" data-bs-toggle="tab"
                                            href="#profile_tab_<?php echo e($section->id); ?>" role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti-view-list me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">
                                                        <?php echo e(!empty($section->content_value['name']) ? $section->content_value['name'] : $section->title); ?>

                                                    </h5>
                                                    <small class="text-muted"> <?php echo e($section->title); ?>

                                                        <?php echo e(__('Section Settings')); ?></small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <div class="col-lg-8">
                            <?php if(Gate::check('edit additional')): ?>
                                <div class="tab-content">
                                    <?php $__currentLoopData = $additionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="tab-pane <?php echo e(empty($activeTab) || $activeTab == 'profile_tab_' . $section->id ? ' active show ' : ''); ?>"
                                            id="profile_tab_<?php echo e($section->id); ?>" role="tabpanel"
                                            aria-labelledby="footer_column_1">
                                            <?php echo e(Form::model($section, ['route' => ['additional.update', $section->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data'])); ?>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?php echo e(Form::label('name', __('Name'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[name]', !empty($section->content_value['name']) ? $section->content_value['name'] : $section->title, ['class' => 'form-control', 'placeholder' => __('Enter Section name')])); ?>

                                                    </div>
                                                </div>


                                                <?php if($section->section == 'Section 0'): ?>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label'])); ?>

                                                        <div class="form-check form-switch">
                                                            <?php echo e(Form::hidden('content_value[section_enabled]', 'deactive')); ?>

                                                            <?php echo e(Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled'])); ?>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('title', __('Title'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[title]', !empty($section->content_value['title']) ? $section->content_value['title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Section name')])); ?>

                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('sub_title', __('Sub Title'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[sub_title]', !empty($section->content_value['sub_title']) ? $section->content_value['sub_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter sub title')])); ?>

                                                    </div>

                                                    <div class="col-md-4 form-group">
                                                        <?php echo e(Form::label('banner_image1', __('Main Image'), ['class' => 'form-label'])); ?>

                                                        <a href="<?php echo e(asset(Storage::url($section->content_value['banner_image1_path']))); ?>"
                                                            target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        <?php echo e(Form::file('content_value[banner_image1]', ['class' => 'form-control'])); ?>

                                                    </div>
                                                <?php endif; ?>


                                                

                                                <?php if($section->section == 'Section 2'): ?>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label'])); ?>

                                                        <div class="form-check form-switch">
                                                            <?php echo e(Form::hidden('content_value[section_enabled]', 'deactive')); ?>

                                                            <?php echo e(Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled'])); ?>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('sec2_title', __('Title'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[sec2_title]', !empty($section->content_value['sec2_title']) ? $section->content_value['sec2_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Section name')])); ?>

                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('sec2_sub_title', __(key: 'Sub Title'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[sec2_sub_title]', !empty($section->content_value['sec2_sub_title']) ? $section->content_value['sec2_sub_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter sub title')])); ?>

                                                    </div>

                                                    <div class="col-md-4 form-group">
                                                        <?php echo e(Form::label('sec2_banner_image', __('Banner Image'), ['class' => 'form-label'])); ?>

                                                        <a href="<?php echo e(asset(Storage::url($section->content_value['sec2_banner_image_path']))); ?>"
                                                            target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        <?php echo e(Form::file('content_value[sec2_banner_image]', ['class' => 'form-control'])); ?>

                                                    </div>
                                                <?php endif; ?>

                                                <?php if($section->section == 'Section 3'): ?>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('enabled_email', __('Section Enabled'), ['class' => 'form-label'])); ?>

                                                        <div class="form-check form-switch">
                                                            <?php echo e(Form::hidden('content_value[section_enabled]', 'deactive')); ?>

                                                            <?php echo e(Form::checkbox('content_value[section_enabled]', 'active', !empty($section->content_value['section_enabled']) && $section->content_value['section_enabled'] == 'active' ? true : false, ['class' => 'form-check-input', 'role' => 'switch', 'id' => 'section_enabled'])); ?>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('sec3_title', __('Title'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[sec3_title]', !empty($section->content_value['sec3_title']) ? $section->content_value['sec3_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Section name')])); ?>

                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <?php echo e(Form::label('sec3_sub_title', __('Sub Title'), ['class' => 'form-label'])); ?>

                                                        <?php echo e(Form::text('content_value[sec3_sub_title]', !empty($section->content_value['sec3_sub_title']) ? $section->content_value['sec3_sub_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter sub title')])); ?>

                                                    </div>

                                                    <div class="col-md-4 form-group">
                                                        <?php echo e(Form::label('sec3_banner_image', __('Banner Image'), ['class' => 'form-label'])); ?>

                                                        <a href="<?php echo e(asset(Storage::url($section->content_value['sec3_banner_image_path']))); ?>"
                                                        target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        <?php echo e(Form::file('content_value[sec3_banner_image]', ['class' => 'form-control'])); ?>

                                                    </div>
                                                <?php endif; ?>





                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-6">


                                                </div>
                                                <div class="col-6 text-end">
                                                    <input type="hidden" name="tab"
                                                        value="profile_tab_<?php echo e($section->id); ?>">
                                                    <?php echo e(Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded'])); ?>

                                                </div>
                                            </div>
                                            <?php echo e(Form::close()); ?>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5><?php echo e(__('Blog')); ?></h5>
                        </div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create blog')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="xl"
                                    data-url="<?php echo e(route('blog.create')); ?>" data-title="<?php echo e(__('Create New blog')); ?>">
                                    <i class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create blog')); ?></a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th class="w-20"><?php echo e(__('Image')); ?></th>
                                    <th><?php echo e(__('Title')); ?></th>
                                    <th><?php echo e(__('Content')); ?></th>
                                    <th><?php echo e(__('Enable')); ?></th>
                                    <?php if(Gate::check('edit blog') || Gate::check('delete blog')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(!empty($blog->image) && !empty($blog->image)): ?>
                                        <?php $image= $blog->image; ?>
                                    <?php else: ?>
                                        <?php $image= 'default.png'; ?>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="w-20"> <img src="<?php echo e(asset(Storage::url('upload/blog/image')) . '/' . $image); ?>"
                                                alt="<?php echo e($blog->name); ?>" style="width:60px; height:60px;" /></td>
                                        <td> <?php echo e(ucfirst($blog->title)); ?> </td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit(strip_tags($blog->content), 50, '...')); ?>

                                        </td>
                                        <td>

                                            <?php if($blog->enabled == 1): ?>
                                                <span class="d-inline badge text-bg-success"><?php echo e(__('Enable')); ?></span>
                                            <?php else: ?>
                                                <span class="d-inline badge text-bg-danger"><?php echo e(__('Disable')); ?></span>
                                            <?php endif; ?>

                                        </td>
                                        <?php if(Gate::check('edit blog') || Gate::check('delete blog')): ?>
                                            <td>
                                                <div class="cart-action">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['blog.destroy', $blog->id]]); ?>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit blog')): ?>
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                            data-bs-toggle="tooltip" data-size="lg"
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                            data-url="<?php echo e(route('blog.edit', $blog->id)); ?>"
                                                            data-title="<?php echo e(__('Edit blog')); ?>"> <i data-feather="edit"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete blog')): ?>
                                                        <a class=" avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="<?php echo e(__('Detete')); ?>" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    <?php endif; ?>
                                                    <?php echo Form::close(); ?>

                                                </div>

                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/additional/index.blade.php ENDPATH**/ ?>