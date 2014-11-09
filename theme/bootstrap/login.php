<?php if (!class_exists('pepatung')) die(); ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.png">

    <title><?php echo $this->pageTitle(); ?></title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $this->call("themepath")."/".$this->call("theme"); ?>/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="<?php echo $this->call("themepath")."/".$this->call("theme"); ?>/css/bootstrap-signin.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="<?php echo $this->call("themepath")."/".$this->call("theme"); ?>/js/html5shiv.js"></script>
      <script src="<?php echo $this->call("themepath")."/".$this->call("theme"); ?>/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

	<?php $this->loadOutput(); ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
  </body>
</html>
