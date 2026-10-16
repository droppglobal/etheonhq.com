<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('You are offline')); ?></title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f6f7f9; color: #1f2937; }
        .box { text-align: center; padding: 32px; max-width: 360px; }
        img { width: 72px; height: 72px; border-radius: 16px; }
        button { margin-top: 16px; padding: 10px 20px; border: 0; border-radius: 8px; background: #2ca58d; color: #fff; font-size: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <img src="<?php echo e(asset('assets/images/pwa/icon-192.png')); ?>" alt="">
        <h2><?php echo e(__('You are offline')); ?></h2>
        <p><?php echo e(__('Check your internet connection and try again.')); ?></p>
        <button onclick="location.reload()"><?php echo e(__('Try again')); ?></button>
    </div>
</body>
</html>
<?php /**PATH /home/etheonbe/public_html/resources/views/pwa/offline.blade.php ENDPATH**/ ?>