<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div class="tabw">
<div class="btabw">

<?php echo $this->fetch('content'); ?>

</div>
</div>
<?php echo $this->Session->flash(); ?>
<script>
$(function() {
	AlertShow();
});
</script>

<?php echo $this->element('sql_dump'); ?>
