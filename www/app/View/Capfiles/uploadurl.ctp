<!--
   CapAnalysis

   Copyright 2016 Gianluca Costa (http://www.capanalysis.net

  License: GNU GPL
   
-->
<?php if ($limit_on): ?>
<div class="outcome">
	<div class="outcome-bord">
        <h2><?php echo __('PCAP file size limit reached'); ?></h2>
    </div>
</div>
<?php else: ?>
<div class="outcome">
	<div class="outcome-bord" id="newdataset">
        <h2><?php echo __('Import from URL (pcap file or zip file with pcap files inside)'); ?></h2><br/><br/>
		<div>
			<?php echo $this->Form->create('Capfile', array('url' => 'uploadurl', 'id'=>'jform')); ?>
			<?php echo $this->Form->input('url', array('maxlength'=>'140', 'label' => __('URL').': ')); ?>
            <div id="zip_password" class="dispoff">
            <?php echo $this->Form->input('password', array('maxlength'=>'140', 'label' => __('ZIP password').': ')); ?>
            </div>
            <?php echo $this->Form->end(__('Submit')); ?><br/>
        </div>
	</div>
</div>
<div class="outcome dispoff" id="waitupload">
	<div class="outcome-bord">
    <h2><?php echo __('Upload in progress... please wait'); ?></h2>
	</div>
</div>

<?php endif; ?>
<script>
$(function() {
    $("#CapfileUrl").on('input', function() {
        var url = $(this).val();
        var ext = url.slice((url.lastIndexOf(".") - 1 >>> 0) + 2);
        if (ext == 'zip' || ext == 'ZIP') {
            $('#zip_password').show(400);
        }
        else {
            $('#CapfilePassword').val('');
            $('#zip_password').hide(100);
        }
    });
    $('input:submit').click(function() {
        $('#newdataset').hide(100);
        $('#waitupload').show(400);
    });
	$('input:submit').button();
});
</script>
