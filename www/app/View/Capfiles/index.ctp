<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php
$this->Html->script('raphael-min', array('inline' => false));
$this->Html->script('g.raphael-min', array('inline' => false));
$this->Html->script('g.pie-min', array('inline' => false));
$this->Html->script('jquery.iframe-transport', array('inline' => false));
$this->Html->script('jquery.fileupload', array('inline' => false));
?>
<div class="grid_9 demo_full">
    <div id="tabs" class="tabs-nohdr tbl">
    <ul>
        <li><a href="#tabs-1" title="<?php echo __('List of All pcap files');?>"><?php echo __('PCAP Files'); ?></a></li>
        <li><a href="<?php echo $this->Html->url(array('controller' => 'capfiles', 'action' => 'uploadurl')); ?>" title="<?php echo __('Import from URL'); ?>">Import from URL</a></li>
        <li><a href="<?php echo $this->Html->url(array('controller' => 'capfiles', 'action' => 'pcapoverip')); ?>" title="<?php echo __('PCAP-over-IP connection info'); ?>">PCAP-over-IP</a></li>
        <li><a href="<?php echo $this->Html->url(array('controller' => 'capfiles', 'action' => 'sharedfolder')); ?>" title="<?php echo __('Windows shared folder'); ?>">Shared Folder</a></li>
    </ul>
    <div id="tabs-1">
        <table cellpadding="0" cellspacing="0" class="fixed">
        <tr>
            <th class="name"><?php echo $this->Paginator->sort('filename', __('File Name', true), array('title' => __('Sort by file name', true))); ?></th>
            <th class="size"><?php echo $this->Paginator->sort('data_size', __('Size', true), array('title' => __('Sort by size', true))); ?></th>
            <th class="size"></th>
            <th><?php echo $this->Paginator->sort('md5', __('MD5', true), array('title' => __('Sort by MD5', true))); ?></th>
            <th><?php echo $this->Paginator->sort('sha1', __('SHA', true), array('title' => __('Sort by SHA', true))); ?></th>
            <th class="txt-cent"><?php echo __('Actions'); ?></th>
        </tr>
        <?php
        foreach ($capfiles as $capfile): ?>
        <tr>
            <td><?php echo h($capfile['Capfile']['filename']); ?>&nbsp;</td>
            <td><?php echo h($this->String->size($capfile['Capfile']['data_size'])); ?>&nbsp;</td>
            <td class="cursor"><?php echo $this->Html->image('pie_ds.png', array('alt' => '', 'title' => __('Flow & Protocols Info'), 'class' => 'jpie', 'data-id' => $capfile['Capfile']['id']));?></td>
            <td title="<?php echo $capfile['Capfile']['md5']; ?>"><?php echo h($capfile['Capfile']['md5']); ?>&nbsp;</td>
            <td title="<?php echo $capfile['Capfile']['sha1']; ?>"><?php echo h($capfile['Capfile']['sha1']); ?>&nbsp;</td>
            <td class="actions">
                <?php if (!$this->Session->check('demo')): ?>
                <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $capfile['Capfile']['id']), null, __('Are you sure you want to delete "%s"?', $capfile['Capfile']['filename'])); ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </table>

        <div class="page-bar">
            <div class="page-bar-bord">
                <div class="paging">
                <?php
                    echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
                    echo $this->Paginator->numbers(array('separator' => ''));
                    echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
                ?>
                </div>
                <?php $page_info = $this->Paginator->params();?>
                <?php if ($page_info['pageCount'] > 9): ?>
                <div class="page-cursor">
                    <input id="dial" type="text" value="<?php echo $page_info['page']; ?>" data-width="50" data-min="1" data-max="<?php echo $page_info['pageCount']?>">
                </div>
                <div class="paging">
                    <span id="gopage" class="alone disabled"><a href="#"><?php echo __('Go'); ?></a></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
    </div>
    <div class="clear">&nbsp;</div>
    <div class="tbl">
    <table cellpadding="0" cellspacing="0" class="fixed dispoff first" id="fupload" title="<?php echo __('The analysis will be start from some seconds...'); ?>">
        <tr>
            <th class="name"><?php echo __('Files Uploaded'); ?></th>
            <th class="name"></th>
        </tr>
    </table>
    </div>
    <div class="clear">&nbsp;</div>
</div>

<div class="grid_3 demo_full">
    <div class="first dashed">
        <div class="bgblue">            
            <div id="upload">
                <?php echo $this->Html->image('clowdup.png', array('alt' => '')); ?><br/>
                <?php echo __('Drag and drop here');?><br/>
                <?php echo __('the PCAP files or');?><br/>
                <div class="inbutton">
                <input id="fileupload" type="file" name="files[]" data-url="<?php echo $this->Html->url(array('controller' => 'capfiles', 'action' => 'add')); ?>" multiple></input>
                <?php echo __('Click here'); ?></div><br/>
                <div class="small"><?php echo __('Max Size').': '.$max_size;?></div><br/>
                <div id="progressbar" class="dispoff"></div>
            </div>
        </div>
    </div>
</div>
<div class="clear">&nbsp;</div>
<div class="grid_12 dispoff" id="demo_full">
<div class="outcome">
    <div class="outcome-bord">
        <h2><?php echo __('PCAP file size limit reached'); ?></h2>
    </div>
</div>
</div>
<div class="clear">&nbsp;</div>
<script>
$(function() {
    var upload = 0, uplpos = 1;
    var files_num = <?php echo $tot_files; ?>;
    
    if (<?php echo $limit_on; ?>) {
        $("#demo_full").show();
        $(".demo_full").hide();
        return;
    }
    
    $("#dial").dial({
        'angleArc': 250,
        'angleOffset': -125,
        'bgColor':"#DFEDED",
        'fgColor':"#3474EF",
        'change' : function (v) {
            $('#gopage').removeClass('disabled');
            $('#gopage a').attr("href", "<?php echo $this->Html->url(array('controller' => 'capfiles', 'action' => 'index', 'page')); ?>:"+v);}
        });
    $("#tabs").tabs();
    $('a[title], td[title], #fupload').qtip({position: {my: 'bottom center', at: 'top center'}, style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'}});
    $('.jpie').each(function() {
        $(this).qtip({position: {my: 'left center', at: 'right center'},
                    style: {classes: 'ui-tooltip-shadow ui-tooltip-light'},
                    content: {text: 'Loading...', ajax: {url: '<?php echo $this->Html->url(array('controller' => 'capfiles', 'action' => 'datatip')); ?>/'+$(this).attr("data-id") }, title: {text: 'File Report', button: true}},
                    show: {event: 'click',  solo: true},
                    hide: 'unfocus'
        });
    });
    $('.actions a, .inbutton').button();
    $( "#progressbar" ).progressbar({
        value: 0
    });
    
    $('#fileupload').fileupload({
        dataType: 'json',
        add: function (e, data) {
            upload++;
            $('#progressbar').fadeIn(300);
            data.submit();
        },
        done: function (e, data) {
            upload--;
            $('#fupload').fadeIn(300);
            if (upload == 0) {
                $('#progressbar').progressbar({
                    value: 100
                });
            }
            $.each(data.result, function (index, file) {
                if (uplpos) {
                    $('<tr><td>'+file.name+'</td><td></td></tr>').appendTo('#fupload');
                    uplpos = 0;
                }
                else {
                    $('#fupload tr:last td:last').append().text(file.name);
                    uplpos = 1;
                }
            });
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progressbar').progressbar({
                value: progress
            });
        }
    });
});
</script>
