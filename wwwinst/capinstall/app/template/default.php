<?php
    /* Template:
        Variabili della pagina:
            $title_page     -> titolo da dare alla pagina
            $custom_js      -> path (anche in array) del file Javascript della pagina
            $custom_css     -> path (anche in array) del file CSS della pagina
            $header_left    -> menu' nel header a sinistra
            $header_right   -> menu' nel header a destra
            $menuleft       -> menu' nel lato sinistro (e' una colonna che copre tutto il lato sinistro)
        Suddivisione della pagina:
            header  -> header della pagina
                banner --> contiene il banner (definito nel css)
                
                menu   --> menu' orrizontali pesenti al top della pagina
                    h_left  ---> menu' con voci a sinistra della pagina
                        $header_left: array(array('help' => 'Help per il link', 'link' => 'link', 'title' => 'titolo del link'), ...)
                    h_right ---> menu' con voci a destra della pagina
                        $header_right: array(array('help' => 'Help per il link', 'link' => 'link', 'title' => 'titolo del link'), ...)
                        
            mleft   -> menu' di sinistra, presente solo se il menu' e' settato
                $menuleft: array('active' => '1', 'sections' => array(array('name' => 'Primo', 'sub' => array(array('name' => 'Sub1', 'link' => '/', 'help' => 'Torna alla pagina iniziale'), array('name' => 'sub2')))));
                
            pbody   -> corpo principale della pagina (la sua dimensione dipende dalla presenza o meno di mleft
                $page_content: contenuto della pagina (con div, ect)
                
            footer  -> footer della pagina
    */
    /* esempi:   
    $header_left = array(array('help' => 'Help per il link', 'link' => 'link', 'title' => 'Titolo del link'), array( 'link' => 'link', 'title' => 'Titolo del link'));
    $header_right = array(array('help' => 'Cavolo', 'link' => 'link', 'title' => 'Titolo del link'), array( 'link' => 'link', 'title' => 'Titolo del link'));
    */
?>
<!DOCTYPE html>
<html><head>
	<meta http-equiv="content-type" content="text/html;">
    <meta charset="utf-8">
    <title><?php echo $title_page; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta content="Gianluca Costa" name="author"/>

    <!-- Le styles -->
    <link href="<?php echo $ROOT_APP; ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ROOT_APP; ?>css/base_tmpl.css" type="text/css" media="all" rel="stylesheet" />
    <link href="<?php echo $ROOT_APP; ?>css/bootstrap-responsive.min.css" rel="stylesheet">
    <?php
        if (isset($custom_css)) {
            if (is_array($custom_css)) {
                foreach ($custom_css as $c_css) {
                    echo '<link href="'.$ROOT_APP.'css/'.$c_css.'" type="text/css" media="all" rel="stylesheet" />';
                }
            }
            else {
                echo '<link href="'.$ROOT_APP.'css/'.$custom_css.'" type="text/css" media="all" rel="stylesheet" />';
            }
        }
    ?>
    <link href="<?php echo $ROOT_APP; ?>css/firebug.css" rel="stylesheet">
    
    <!-- Le javascript
    ================================================== -->
    <script src="<?php echo $ROOT_APP; ?>js/jquery-1.8.3.min.js"></script>
    <script src="<?php echo $ROOT_APP; ?>js/bootstrap.min.js"></script>  
    <script src="<?php echo $ROOT_APP; ?>js/base_tmpl.js"></script>  
    <?php
        if (isset($custom_js)) {
             if (is_array($custom_js)) {
                foreach ($custom_js as $c_js) {
                    echo '<script type="text/javascript" src="'.$ROOT_APP.'js/'.$c_js.'"></script>';
                }
            }
            else {
                echo '<script type="text/javascript" src="'.$ROOT_APP.'js/'.$custom_js.'"></script>';
            }
        }
    ?>
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="<?php echo $ROOT_APP; ?>js/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo $ROOT_APP; ?>images/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo $ROOT_APP; ?>images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $ROOT_APP; ?>images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $ROOT_APP; ?>images/apple-touch-icon-114x114.png">
  </head>

  <body class="">
	<div id="message_box">
		<div id="alert"><?php if (isset($esalert)) echo $esalert;?></div>
	</div>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="<?php echo $ROOT_APP; ?>">CapInstall</a>
			<div class="nav-collapse collapse">
            <?php if (isset($header_left)) : ?>
            <ul class="nav">
			  <?php $i = 0; ?>
              <?php foreach ($header_left as $voce): ?>
              <?php if ($i == $header_left_active): ?>
              <li class="active">
              <?php else: ?>
              <li>
              <?php endif; ?>
				<a title="<?php echo $voce['help']; ?>" href="<?php echo $voce['link']; ?>"><?php echo $voce['title']; ?></a>
			  </li>
			  <?php $i++; ?>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if (isset($header_right)) : ?>
            <ul class="nav pull-right">
			  <?php $i = 0; ?>
              <?php foreach ($header_right as $voce): ?>
              <?php if ($i == $header_right_active): ?>
              <li class="active">
              <?php else: ?>
              <li>
              <?php endif; ?>
              <a title="<?php echo $voce['help']; ?>" href="<?php echo $voce['link']; ?>"><?php echo $voce['title']; ?></a>
              </li>
	          <?php $i++; ?>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row">
        <?php if (isset($menuleft)) : ?>
        <div class="span3 bs-docs-sidebar">
		  <ul class="nav nav-list bs-docs-sidenav affix">
			<?php $i = 0; ?>
            <?php foreach ($menuleft['sections'] as $section): ?>
                <?php foreach ($section['sub'] as $submenu): ?>
                <?php if ($i == $menuleft['active']): ?>
                <li class="active">
                <?php else: ?>
				<li>
                <?php endif; ?>
					<a title="<?php echo $submenu['help']; ?>" href="<?php echo $submenu['link']; ?>"><?php echo $submenu['name']; ?>
					<i class="icon-chevron-right"></i>
					</a>
				</li>
				<?php $i++; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
		  </ul>
        </div><!--/span-->
        <div class="span9">
        <?php else: ?>
        <div class="span12">
        <?php endif; ?>
        <?php echo $page_content; ?>
        </div><!--/span-->
      </div><!--/row-->
    </div><!--/container-->
    <footer>
        <p>&copy; 2012-2016 <a href="http://www.capanalysis.net">CapAnalysis</a>. All Rights Reserved.</p>
    </footer>
</body></html>
