<?php 
require "_init.php";

$form_action = $_GET['FormAction'] ?? $_POST['FormAction'];

if ($form_action == 'Save') {
    // slug cleanup -- replace spaces with underscores, remove invalid characters, combine multiple underscores into single underscores
    $slug_clean = strtolower(preg_replace('/_+/', '_', preg_replace('/[^\w-]/', '', str_replace(' ', '_', $_POST['Slug']))));

    $pdb->add_blog_post($_POST['Y'], $_POST['M'], $_POST['D'], $slug_clean, $_POST['Title'], $_POST['LinkURL'], $_POST['Content'], $pdb->authenticated_as['Username'], $pdb->authenticated_as['Name']);

    header("Location: {$pdb->blog_full_base_url}/{$_POST['Y']}/{$_POST['M']}/{$_POST['D']}/{$slug_clean}");
} 

$blog_post = $pdb->get_blog_post(
    $_GET['Y'] ?? $_POST['Y'], 
    $_GET['M'] ?? $_POST['M'], 
    $_GET['D'] ?? $_POST['D'], 
    $_GET['Slug'] ?? $_POST['Slug']);

if ($blog_post == NULL) {
    $default_tz = date_default_timezone_get();
    date_default_timezone_set($pdb->tz);
    $blog_post = [
        'Y' => date("Y"),
        'M' => date("m"),
        'D' => date("d")
    ];
    date_default_timezone_set($default_tz);
    $edit_or_new = 'New';
} else {
    $edit_or_new = 'Edit';
}

?>

<?php require "_header.php" ?>

<script type="text/javascript">
$(document).ready(function(){
    $("#ContentPreview").click(
        function(){ 
            $("#inputTitlePreview").val($("#inputTitle").val());
            $("#inputMarkdownPreview").val($("#inputContent").val());
            $("#previewForm").submit();
        }
    );
});
</script>

<?php if ($edit_or_new == 'Edit') { ?>
    <h1>Edit Post</h1>
    <h5><?=$blog_post['RelativeURL']?></h5>
<?php } else { ?>
    <h1>New Post</h1>
<?php } ?>

<form name="addEditForm" action="./add_edit_post.html" method="POST">
  <input type="hidden" name="FormAction" value="Save">
  <input type="hidden" name="Y" value="<?=$blog_post['Y']?>">
  <input type="hidden" name="M" value="<?=$blog_post['M']?>">
  <input type="hidden" name="D" value="<?=$blog_post['D']?>">
  <input type="hidden" name="Slug" value="<?=$blog_post['Slug']?>">

<?php if ($edit_or_new == 'New') { ?>
  <div class="form-group">
    <div>Slug</div>
    <input type="text" name="Slug" class="form-control" id="inputSlug" aria-describedby="slugHelp" placeholder="a_succinct_title" value="<?=$blog_post['Slug']?>" maxlength="100" required>
    <small id="slugHelp" class="form-text text-muted">(Letters, numbers, spaces only. All letters will be lowercased. Any spaces will be translated to underscores.)</small>
  </div>
<?php } ?>
  <div class="form-group">
    <label for="inputTitle">Title</label>
    <input type="text" name="Title" class="form-control" id="inputTitle" placeholder="Post Title" value="<?=$blog_post['Title']?>" required>
  </div>
  <div class="form-group">
    <label for="inputLinkURL">Link URL</label>
    <input type="text" name="LinkURL" class="form-control" id="inputLinkURL" aria-describedby="linkURLHelp" placeholder="http://" value="<?=$blog_post['LinkURL']?>">
    <small id="linkURLHelp" class="form-text text-muted">(Optional.)</small>
  </div>
  <div class="form-group">
    <label for="inputContent">Content</label>
    <a href="https://www.markdownguide.org/cheat-sheet" target="_blank" class="badge badge-primary">Markdown Reference</a>
    <a href="#" id="ContentPreview" class="badge badge-primary">Preview</a>
    <textarea name="Content" class="form-control" id="inputContent" rows="10" required><?=$blog_post['Content']?></textarea>
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary"><?php echo ($edit_or_new == 'Edit') ? 'Update' : 'Add Post'; ?></button>
  </div>
</form>

<form name="PreviewForm" id="previewForm" action="./markdown_preview.html" method="POST" target="_blank">
<input type="hidden" name="Title" id="inputTitlePreview">
<input type="hidden" name="MarkdownText" id="inputMarkdownPreview">
</form>

<?php require '_footer.php' ?>
