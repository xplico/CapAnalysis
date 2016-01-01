<?php echo $this->fetch('content'); ?>
<?php echo $this->Session->flash(); ?>
<script>
$(function() {
	AlertShow();
});
</script>

<?php echo $this->element('sql_dump'); ?>
