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


$tinymceInit = '<script>
    tinymce.init({
        selector: "textarea",
        license_key: "gpl",
        plugins: "code, image",
        toolbar: [
            { name: "history", items: [ "undo", "redo" ] },
            { name: "styles", items: [ "styles" ] },
            { name: "formatting", items: [ "bold", "italic" ] },
            { name: "alignment", items: [ "alignleft", "aligncenter", "alignright", "alignjustify", "bullist" ] },
            { name: "indentation", items: [ "outdent", "indent" ] },
            { name: "expert", items: [ "image", "code" ] }
        ],
        image_list: [';
            
            if(isset($_GET["id"])) {
                $id = $_GET["id"];
                if(is_dir("uploads/" . $id)) {
                    $images = scandir("uploads/" . $id);
                    foreach($images as $image) {
                        if($image !== "." && $image !== "..") {
                            $tinymceInit .= "{ title: \"" . $image . "\", value: \"uploads/" . $id . "/" . $image . "\" },";
                        }
                    }
                }
            }
            
        
  $tinymceInit .=' ],     image_class_list: [
            { title: "None", value: "" },
            { title: "Float Start", value: "img-fluid float-start" },
            { title: "Float End", value: "img-fluid float-end" }
        ]
    });
</script>';


function createMainNav() {
    $nav = [
        ["title" => "Home", "url" => "index.php"],
        ["title" => "Neuen Beitrag", "url" => "index.php?action=add"]
    ];
    return $nav;
}
function createTopRightNav() {
    $nav = [
        ["title" => "Home", "url" => "index.php"],
        ["title" => "Impressum", "url" => "index.php?action=add"]
    ];
    return $nav;
}

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

if(isset($_GET['action']) && $_GET['action'] === "view") {
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $result = $conn->query("SELECT * FROM posts WHERE id = " . intval($id));
        if($result->num_rows > 0) {
            $post = $result->fetch_assoc();
    $body = ' <article class="main-card">
          <h1 class="entry-title">' . htmlspecialchars($post['title']) . '</h1>
          <div class="entry-meta">
            ' . htmlspecialchars($post['published']) . ' by <a href="#">' . htmlspecialchars('Faker 3000') . '</a> — <a href="#">8 Comments</a> (<a href="#">Edit</a>)
          </div>
          
          <!-- Platzhalter für Beitragsbild -->
           <div class="featured-image-holder text-center text-muted">
             <div>
               <i class="bi bi-laptop display-1"></i>
               <p class="mt-2">[ Beitragsbild Placeholder ]</p>
             </div>
           </div>
          
          <div class="entry-content">
            ' . $post['content'] . '
          </div>
        </article>';
        }
    }
}


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

$postHtml.='<article class="post-card">
          <h2 class="entry-title"><a href="index.php?action=view&id=' . $post['id'] . '">' . htmlspecialchars($post['title']) . '</a></h2>
          <div class="entry-meta">
            ' . htmlspecialchars($post['published']) . ' by <a href="#">Kung Fu Panda</a> — <a href="#">8 Comments</a>
          </div>';
        //   $postHtml.='<div class="featured-image-holder">
        //     <div class="text-center">
        //       <i class="bi bi-laptop display-4"></i>
        //       <p class="mb-0 mt-1">[ Visual Content / Laptop Image ]</p>
        //     </div>
        //   </div>';
          $postHtml.=' ' . $post['content'] . '<br>
          <a href="index.php?action=view&id=' . $post['id'] . '" class="btn-read-more">Lesen»</a>
          <a href="index.php?action=edit&id=' . $post['id'] . '" class="btn-read-more">Bearbeiten »</a>
        </article>';

        //    $postHtml.='<h2>' . htmlspecialchars($post['title']) . '</h2>';
        //    $postHtml.='' . $post['content'] . '';
        //    $postHtml.='<small>Veröffentlicht am: ' . htmlspecialchars($post['published']) . '</small>';
        //    $postHtml.='<a href="index.php?action=edit&id=' . $post['id'] . '">bearbeiten</a>';
        //    $postHtml.='<hr>';
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
        $body.='<nav aria-label="Page navigation" class="mb-4">
                <ul class="pagination  justify-content-center">';
                if(($page ?? 1) > 1) {
                    $body.='<li class="page-item">
                        <a class="page-link" href="index.php?page=' . (($page ?? 1) - 1) . '" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                    </li>';
                }
                    for($i = 1; $i <= $totalPages; $i++) {
                        $body.='<li class="page-item ' . (($i == $page) ? 'active' : '') . '"><a class="page-link" href="index.php?page=' . $i . '">' . $i . '</a></li>';
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
    if(!isset($_GET['action'])) {
    $page = $_GET['page'] ?? 1;
    $totalPages = getTotalPages($conn, $perPage);
    if($totalPages > 1) {
        $pagination = displayPagination($page, $totalPages);
        $body .= $pagination;
    }
    }

?> 
<?php include 'theme/theme.php'; ?>

<?php
$conn->close();
?>