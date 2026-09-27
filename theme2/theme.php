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
    
 
</body>
</html>