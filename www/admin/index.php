<?php require "_init.php" ?>

<?php require "_header.php" ?>

<?php 
    $md = <<<EOT
# Admin Console

## [New Post](./add_edit_post.html)
## [Edit Post](../)
## [Logout](./logout.html)
EOT;

    use Michelf\MarkdownExtra;
    echo(MarkdownExtra::defaultTransform($md));
?>

<?php require '_footer.php' ?>
