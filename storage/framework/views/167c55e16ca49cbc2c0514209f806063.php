

<?php
    $statusName = $status ?? 'Unknown';
    $displayType = $type ?? 'text';
?>

<?php if($displayType === 'badge'): ?>
    <span class="badge bg-secondary"><?php echo e($statusName); ?></span>
<?php else: ?>
    <span class="status-text" style="font-size: 14px; font-weight: 500;"><?php echo e($statusName); ?></span>
<?php endif; ?>
<?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/partials/status-display.blade.php ENDPATH**/ ?>