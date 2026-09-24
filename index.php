<?php 
require_once 'config.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


$title = "Simple Blog"; 
$body = "Loading...";
$header = "";
$footer = "";
$sidebar = "";


function displayForm($title="", $content="") {
 $form = '<form method="post" >';
    $form .= '<div class="form-group"><label for="title">Überschrift</label>';
    $form .= '<input class="form-control" type="text" name="title" id="title" value="' . htmlspecialchars($title) . '">';
    $form .= '</div>';
    $form .= '<br>';
    $form .= '<div class="form-group"><label for="content">Inhalt</label>';
    $form .= '<textarea class="form-control" name="content" id="content">' . htmlspecialchars($content) . '</textarea>';
    $form .= '</div>';
    $form .= '<br>';
    $form .= '<input type="submit" class="btn btn-primary mb-3 " value="speichern">';
    $form .= '</form>';
    return $form;
}


if(isset($_GET['action']) && $_GET['action'] === "add") {
    
    $body = displayForm();
}

function displayLastBlogPosts($posts) {
    $postHtml = '<ul>';
    foreach($posts as $post) {
        $postHtml .= '<li>';
        $postHtml .= '<a href="index.php?action=view&id=' . intval($post['id']) . '">';
        $postHtml .= '' . $post['title'] . '';
        $postHtml .= '</a>';
        $postHtml .= '<hr>';
        $postHtml .= '</li>';
    }
    $postHtml .= '</ul>';
    return $postHtml;
}
$sidebar .= "<h3>Letzte Beiträge</h3>";
$sidebar .= displayLastBlogPosts(getPosts($conn, 1, 5));

if(isset($_GET['action']) && $_GET['action'] === "edit") {

    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $result = $conn->query("SELECT * FROM posts WHERE id = " . intval($id));
        if($result->num_rows > 0) {
            $post = $result->fetch_assoc();
            $form = displayForm($post['title'], $post['content']); 

            $form .= '<br>';
            $form .= '<h3>Bilder hochladen:</h3>';
            $form.='<form method="post" enctype="multipart/form-data">';
            $form.='<div class="form-group">'; 
            $form.='<label for="file">Bild auswählen</label>';
            $form.='<input type="file" name="file" class="form-control" accept="image/png, image/jpeg">';
            $form.='</div>'; 
            $form.='<br>'; 
            $form.='<input type="submit" class="btn btn-primary mb-3 " value="Upload">';
            $form.='</form>';
        }
    }
    $body = $form;
}


if(isset($_FILES['file']) && isset($_GET['action']) && $_GET['action'] === "edit" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $targetDir = "uploads/" . $id . "/";
    $fileName = basename($_FILES["file"]["name"]);
    $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $fileName);
    if(!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $targetFile = $targetDir . $fileName;
    move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile);

}

if(isset($_POST['title']) && isset($_POST['content'])) {
    if(isset($_GET['action']) && $_GET['action'] === "edit" && isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, published = ? WHERE id = ?");
        $published = date('Y-m-d H:i:s');
        $stmt->bind_param("sssi", $_POST['title'], $_POST['content'], $published, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO posts(title, content, published) VALUES (?, ?, ?)");
        $published = date('Y-m-d H:i:s');
        $stmt->bind_param("sss", $_POST['title'], $_POST['content'], $published);
        $stmt->execute();
        $stmt->close();
    }
}

function getPosts($conn, int $page,  int $limit) {
    $offset = ($page - 1) * $limit;
    $result = $conn->query("SELECT * FROM posts ORDER BY published DESC LIMIT " . intval($offset) . ", " . intval($limit));
    $posts = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    return $posts;
}

function displayBlogPosts($conn, int $page , int $perPage) {
$postHtml = '';
    $posts = getPosts($conn, $page, $perPage);
       foreach($posts as $post) {
           $postHtml.='<h2>' . htmlspecialchars($post['title']) . '</h2>';
           $postHtml.='' . $post['content'] . '';
           $postHtml.='<small>Veröffentlicht am: ' . htmlspecialchars($post['published']) . '</small>';
           $postHtml.='<a href="index.php?action=edit&id=' . $post['id'] . '">bearbeiten</a>';
           $postHtml.='<hr>';
       }
   return $postHtml;    
       }
if(!isset($_GET['action'])) {
$body = displayBlogPosts($conn, $_GET['page'] ?? 1, $perPage);
        
    }

    function getTotalPages( $conn, int $perPage) {
        $result = $conn->query("SELECT COUNT(*) as total FROM posts");
        $row = $result->fetch_assoc();
        $totalPosts = $row['total'];
        return ceil($totalPosts / $perPage);
    }

    function displayPagination(int $page, int $totalPages) {
        $body = '';
        $body.='<nav aria-label="Page navigation">
                <ul class="pagination">';
                if(($page ?? 1) > 1) {
                    $body.='<li class="page-item">
                        <a class="page-link" href="index.php?page=' . (($page ?? 1) - 1) . '" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                    </li>';
                }
                    for($i = 1; $i <= $totalPages; $i++) {
                        $body.='<li class="page-item"><a class="page-link" href="index.php?page=' . $i . '">' . $i . '</a></li>';
                    }

                if(($page ?? 1) < $totalPages) {
                    $body.='<li class="page-item">
                        <a class="page-link" href="index.php?page=' . (($page ?? 1) + 1) . '" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                    </li>';
                }
                    $body.='</ul>
                </nav>';
        return $body;
    }
    $page = $_GET['page'] ?? 1;
    $totalPages = getTotalPages($conn, $perPage);
    if($totalPages > 1) {
        $pagination = displayPagination($page, $totalPages);
        $body .= $pagination;
    }

?> 
<!DOCTYPE HTML>
<html lang="de">
<head>
<title><?php echo $title; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo $header; ?>
    <script src="js/tinymce/tinymce.min.js"></script>
</head>
<body>
    <div class="container ">
    <h1><?php echo $title; ?></h1>

    <header>
        <ul class="nav nav-underline" >
  <li class="nav-item">
    <a class="nav-link <?php echo ($_GET['action'] ?? '') === '' ? 'active' : ''; ?>" aria-current="page" href="index.php">Home</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($_GET['action'] ?? '') === 'add' ? 'active' : ''; ?>" href="index.php?action=add">Neuen Post</a>
  </li>
 
</ul>
      
    </header>

    <div class="row">
    <div class="col-9">
   
    <?php echo $body; ?>
    </div>
    <div class="col-3   ">
        <?php echo $sidebar; ?>
    </div>
    </div>

    
    
    </div>
    <?php echo $footer; ?>
    <script>
        tinymce.init({
            selector: 'textarea',
            license_key: 'gpl',
            plugins: 'code, image',
            // toolbar: 'undo redo | styles | bold italic | link image code',
            toolbar: [
                { name: 'history', items: [ 'undo', 'redo' ] },
                { name: 'styles', items: [ 'styles' ] },
                { name: 'formatting', items: [ 'bold', 'italic' ] },
                
                { name: 'alignment', items: [ 'alignleft', 'aligncenter', 'alignright', 'alignjustify, bullist' ] },
                { name: 'indentation', items: [ 'outdent', 'indent' ] },
                { name: 'expert', items: [ 'image', 'code' ] }
            ],
//   toolbar: 'image',
  image_list: [
    <?php
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        if(is_dir('uploads/' . $id)) {
        $images = scandir('uploads/' . $id);
        foreach($images as $image) {
            if($image !== '.' && $image !== '..') {
                echo "{ title: '" . $image . "', value: 'uploads/" . $id . "/" . $image . "' },";
            }
        }
        }
    }
    ?>
  ]  ,
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Float Start', value: 'img-fluid float-start' },
    { title: 'Float End', value: 'img-fluid float-end' }
  ]    
        });
    </script>
 
</body>
</html>


<?php
$conn->close();
?>