<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $blogTitle; ?></title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="theme/style.css">

  <script src="js/tinymce/tinymce.min.js"></script>
</head>
<body>

  <!-- Header & Sekundäre Navigation -->
  <header class="bg-white">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center py-3">
        <div>
          <a href="index.php" class="site-title d-block"><?php echo $blogTitle; ?></a>
          <span class="site-description"><?php echo $blogDescription; ?></span>
        </div>
        <ul class="nav secondary-nav d-none d-md-flex">
          <?php
          foreach(createTopRightNav() as $navItem) {
              echo '<li class="nav-item"><a class="nav-link" href="' . htmlspecialchars($navItem['url']) . '">' . htmlspecialchars($navItem['title']) . '</a></li>';
          }
          ?>
         
        </ul>
      </div>
    </div>
    
    <!-- Primäre Navigation -->
    <nav class="navbar navbar-expand-lg primary-nav border-top">
      <div class="container">
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNavbar">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="primaryNavbar">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
<?php
foreach(createMainNav() as $navItem) {
    echo '<li class="nav-item"><a class="nav-link" href="' . htmlspecialchars($navItem['url']) . '">' . htmlspecialchars($navItem['title']) . '</a></li>';
}
?>
          </ul>
          <span class="navbar-text fw-bold text-dark d-none d-lg-block">
            <a href="#" class="text-decoration-none text-dark">Party on Wayne!</a>
          </span>
        </div>
      </div>
    </nav>
  </header>

  <!-- Hauptinhalt & Sidebar Layout -->
  <main class="container my-4">
    <div class="row">
      <!-- Blog-Beitrag Spalte -->
      <div class="col-lg-8">
        
      
        <?php echo $body; ?>


       
      </div>
      
      <!-- Sidebar Spalte -->
      <div class="col-lg-4">
        <!-- Newsletter Widget -->
        <!-- <div class="sidebar-widget widget-newsletter">
          <h5>Email Newsletter</h5>
          <p>Sign up to receive email updates and to hear what's going on with our company!</p>
          <form>
            <div class="mb-3">
              <input type="text" class="form-control" placeholder="Your Name">
            </div>
            <div class="mb-3">
              <input type="email" class="form-control" placeholder="E-Mail Address">
            </div>
            <button type="submit" class="btn btn-subscribe">Subscribe</button>
          </form>
        </div> -->
        
        <!-- Social Icons Widget -->
        <!-- <div class="sidebar-widget">
          <h5 class="widget-title">Social icons</h5>
          <div class="social-grid">
            <a href="#" class="social-btn"><i class="bi bi-envelope"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-google"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-pinterest"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-rss"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-vimeo"></i></a>
            <a href="#" class="social-btn"><i class="bi bi-youtube"></i></a>
          </div>
        </div> -->
        
        <!-- Suchfeld Widget -->
        <!-- <div class="sidebar-widget">
          <input type="text" class="form-control search-input" placeholder="Search this website ...">
        </div> -->
        
        <!-- Letzte Beiträge Widget -->
        <div class="sidebar-widget">
          <h5 class="widget-title">Recent Posts</h5>
          <ul class="recent-posts-list">
              <?php 
              foreach(getPosts($conn, 1, 5) as $post) {
                  echo '<li><a href="index.php?action=view&id=' . intval($post['id']) . '">' . htmlspecialchars($post['title']) . '</a></li>';
              }
              ?>
          </ul>
        </div>
      </div>
    </div>
  </main>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php echo $tinymceInit; ?>
</body>
</html>