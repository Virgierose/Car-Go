<?php
// Partial: _steps.php
// Usage: include with $currentStep set (1–5)
// e.g. $currentStep = 2;

$stepLabels = ['Price', 'Driver', 'Details', 'Payment', 'Confirm'];
?>
<div class="steps">
    <?php foreach ($stepLabels as $i => $label):
        $num = $i + 1;
        $class = '';
        if ($num < $currentStep)  $class = 'done';
        if ($num === $currentStep) $class = 'active';
    ?>
        <div class="step <?= $class ?>">
            <div class="step-num"><?= $num < $currentStep ? '✓' : $num ?></div>
            <span class="step-label"><?= $label ?></span>
        </div>
        <?php if ($num < count($stepLabels)): ?>
        <div class="step-line <?= $num < $currentStep ? 'done' : '' ?>"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>