<div class="tabw">
<?php echo $this->fetch('content'); ?>
</div>
<?php echo $this->Session->flash(); ?>
<script>
$(function() {
	AlertShow();
});
</script>
<?php echo $this->element('sql_dump'); ?>
