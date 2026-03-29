<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? \Core\Config::get('business.company_name') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header>
        <h1><?= \Core\Config::get('business.company_name') ?></h1>

    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <p>© <?= date('Y') ?> <?= \Core\Config::get('business.company_name') ?></p>
    </footer>

</body>

</html>