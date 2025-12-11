<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo e(isset($brandingService) ? $brandingService->getAppName() : 'Procurement System'); ?></title>
    <!-- Bootstrap 5.3.8 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(route('branding.css')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css','resources/js/app.js']); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
    <!-- Login screen: collects username/password and posts to login route -->
    <div class="container min-vh-100 d-flex align-items-center">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <?php if(isset($brandingService) && $brandingService->hasLogo()): ?>
                                <div class="mb-3">
                                    <img src="<?php echo e($brandingService->getLogoPath()); ?>" alt="Logo" style="max-height: 80px; max-width: 200px; height: auto;">
                                </div>
                            <?php else: ?>
                                <?php
                                    $primaryColor = isset($brandingService) ? $brandingService->getPrimaryColor() : '#0d6efd';
                                ?>
                                <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;background:<?php echo e($primaryColor); ?>20;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="<?php echo e($primaryColor); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                                </div>
                            <?php endif; ?>
                            <h4 class="fw-semibold mb-0"><?php echo e(isset($brandingService) ? $brandingService->getAppName() : 'Procurement System'); ?></h4>
                            <small class="text-muted"><?php echo e(isset($brandingService) ? $brandingService->getAppTagline() : 'Sign in to your account'); ?></small>
                        </div>

                        <!-- Login form uses Laravel CSRF and our custom auth controller -->
                        <form method="POST" action="<?php echo e(route('login.post')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input class="form-control" type="text" name="username" placeholder="Username" value="<?php echo e(old('username')); ?>" required maxlength="100" />
                                <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input class="form-control" type="password" name="password" placeholder="Password" required maxlength="255" />
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger small"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">
                                    <span class="me-2" n="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5"/><path d="M4 17l5-5-5-5"/></svg>
                                    </span>
                                    Sign in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


<?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/auth/login.blade.php ENDPATH**/ ?>