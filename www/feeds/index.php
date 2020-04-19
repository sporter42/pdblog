<?php require "_init.php" ?>

<?php require "_header.php" ?>

<?php 
    $md = <<<EOT
# Feeds

* [RSS/Atom](./rss/)
EOT;

    use Michelf\MarkdownExtra;
    echo(MarkdownExtra::defaultTransform($md));
?>

<?php require '_footer.php' ?>
