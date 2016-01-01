<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->

<?php if ($l7prot['empty']): ?>
<p>DataSet: <?php echo __('Empty'); ?></p>
<?php else: ?>
<h3><?php echo __('In/Out Traffic:'); ?></h3>
<div id="iotraffic_<?php echo $dsid;?>"></div>
<h3><?php echo __('Top 5 Protocols:'); ?></h3>
<div id="protocols_<?php echo $dsid;?>"></div>
<?php endif; ?>

<script>
$(function() {
	var rt<?php echo $dsid;?> = Raphael("iotraffic_<?php echo $dsid;?>", 180, 70);
	var tpie<?php echo $dsid;?> = rt<?php echo $dsid;?>.piechart(33, 35, 30, [<?php echo $iotraf[0][0]['vin'].','.$iotraf[0][0]['vout']; ?>], { legend: ["%% - In", "%% - Out"], legendpos: "east"});
	tpie<?php echo $dsid;?>.hover(function () {
				this.sector.stop();
				this.sector.scale(1.1, 1.1, this.cx, this.cy);

                if (this.label) {
                        this.label[0].stop();
                        this.label[0].attr({ r: 7.5 });
                        this.label[1].attr({ "font-weight": 800 });
                    }
                }, function () {
                    this.sector.animate({ transform: 's1 1 ' + this.cx + ' ' + this.cy }, 500, "bounce");

                    if (this.label) {
                        this.label[0].animate({ r: 5 }, 500, "bounce");
                        this.label[1].attr({ "font-weight": 400 });
                    }
	});

	var r<?php echo $dsid;?> = Raphael("protocols_<?php echo $dsid;?>", 210, 120);
	var pie<?php echo $dsid;?> = r<?php echo $dsid;?>.piechart(33, 56, 30, [<?php echo $l7prot['num']; ?>], { legend: [<?php echo $l7prot['labels']; ?>], legendpos: "east"});
	pie<?php echo $dsid;?>.hover(function () {
				this.sector.stop();
				this.sector.scale(1.1, 1.1, this.cx, this.cy);

                if (this.label) {
                        this.label[0].stop();
                        this.label[0].attr({ r: 7.5 });
                        this.label[1].attr({ "font-weight": 800 });
                    }
                }, function () {
                    this.sector.animate({ transform: 's1 1 ' + this.cx + ' ' + this.cy }, 500, "bounce");

                    if (this.label) {
                        this.label[0].animate({ r: 5 }, 500, "bounce");
                        this.label[1].attr({ "font-weight": 400 });
                    }
	});
	
});
</script>

