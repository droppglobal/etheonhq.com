<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open Sans" rel="stylesheet" type="text/css">

    <style>
        .body532 blockquote {
            border-left: 5px solid #ccc;
            font-style: italic;
            margin-left: 0;
            margin-right: 0;
            overflow: hidden;
            padding-left: 1.5em;
            padding-right: 1.5em;
        }

        .button {
            background-color: #345C72;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
    </style>
</head>

<?php
    $admin_logo = getSettingsValByName('logo');
?>

<body style="font-family: 'Poppins', Arial, sans-serif">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px;">
                <table class="content" width="600" border="0" cellspacing="0" cellpadding="0"
                    style="border-collapse: collapse; border: 1px solid #cccccc;">
                    <!-- Header -->
                    <tr>
                        <td class="header"
                            style="background-color: #345C72; padding: 40px; text-align: center; color: white; font-size: 24px;">
                            <img src="<?php echo e(!empty($admin_logo) ? fetch_file($admin_logo,'upload/logo/') : '#'); ?>" style="height: 100px;"
                                alt="">
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="body532" style="padding: 40px; text-align: left; font-size: 16px; line-height: 1.6;">
                            <h2><?php echo e(__('Verify Your Email Address')); ?></h2>
                            <p><?php echo e(__('Dear')); ?> <?php echo e($data['name']); ?>,</p>
                            <p><?php echo e(__('Thank you for signing up with')); ?> <strong><?php echo e(env('APP_NAME')); ?></strong>.
                                <?php echo e(__('To complete your registration, please confirm your email address by clicking the button below:')); ?></p>
                            <p style="text-align: center;">
                                <a href="<?php echo e($data['url']); ?>" class="button" target="_blank"><?php echo e(__('Verify Email Address')); ?></a>
                            </p>
                            <p><?php echo e(__('If the button above doesn’t work, copy and paste this URL into your web browser:')); ?></p>
                            <p><a href="<?php echo e($data['url']); ?>"><?php echo e($data['url']); ?>"></a></p>
                            <p><?php echo e(__('This link will expire in 60 minutes.')); ?></p>
                            <p><?php echo e(__('If you didn’t create this account, you can safely ignore this email.')); ?></p>
                            <div class="footer">
                                <p><?php echo e(__('Thank you,')); ?></p>
                                <p><?php echo e(__('The')); ?> <?php echo e(env('APP_NAME')); ?> <?php echo e(__('Team')); ?></p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
<?php /**PATH /home/etheonbe/public_html/resources/views/email/email_verification.blade.php ENDPATH**/ ?>