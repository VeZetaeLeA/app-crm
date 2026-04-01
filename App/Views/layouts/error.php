<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $title ?? 'Error | ' . \Core\Config::get('business.company_name'); ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo htmlspecialchars(\Core\Config::get('typography.font_url')); ?>" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo url('assets/css/variables.css?v=1.0.8'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css?v=1.0.8'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/animations.css?v=1.0.8'); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo url('assets/images/VeZetaeLeA.ico'); ?>">
    <?php
        $ui   = \Core\Config::get('ui') ?? [];
        $typo = \Core\Config::get('typography') ?? [];
    ?>
    <style>
        :root {
            --vzl-font-heading:          <?= htmlspecialchars($typo['font_heading'] ?? "'Sora', sans-serif") ?>;
            --vzl-font-body:             <?= htmlspecialchars($typo['font_body']    ?? "'Inter', sans-serif") ?>;
            --vzl-font-mono:             <?= htmlspecialchars($typo['font_mono']    ?? "'JetBrains Mono', monospace") ?>;
            --vzl-color-bg-dark:         <?= htmlspecialchars($ui['color_bg_dark']          ?? '#030712') ?>;
            --vzl-color-surface-dark:    <?= htmlspecialchars($ui['color_surface_dark']      ?? '#0F172A') ?>;
            --vzl-color-surface-elev:    <?= htmlspecialchars($ui['color_surface_elev']      ?? '#1E293B') ?>;
            --vzl-color-border-dark:     <?= htmlspecialchars($ui['color_border_dark']       ?? 'rgba(255,255,255,0.06)') ?>;
            --vzl-primary:               <?= htmlspecialchars($ui['primary_color']            ?? '#0ea5e9') ?>;
            --vzl-secondary:             <?= htmlspecialchars($ui['secondary_color']          ?? '#c026d3') ?>;
            --vzl-color-gold:            <?= htmlspecialchars($ui['gold_color']               ?? '#D4AF37') ?>;
            --vzl-color-success:         <?= htmlspecialchars($ui['color_success']            ?? '#10B981') ?>;
            --vzl-color-warning:         <?= htmlspecialchars($ui['color_warning']            ?? '#F59E0B') ?>;
            --vzl-color-danger:          <?= htmlspecialchars($ui['color_danger']             ?? '#EF4444') ?>;
            --vzl-color-info:            <?= htmlspecialchars($ui['color_info']               ?? '#3B82F6') ?>;
            --vzl-ui-radius-sm:          <?= htmlspecialchars($ui['radius_sm']               ?? '8px') ?>;
            --vzl-ui-radius-md:          <?= htmlspecialchars($ui['radius_md']               ?? '12px') ?>;
            --vzl-ui-radius-lg:          <?= htmlspecialchars($ui['radius_lg']               ?? '24px') ?>;
            --vzl-ui-radius-pill:        <?= htmlspecialchars($ui['radius_pill']             ?? '50rem') ?>;
        }
    </style>


<body style="background: #0A0A0A; overflow-x: hidden;">
    <main>
        <?php echo $content; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>