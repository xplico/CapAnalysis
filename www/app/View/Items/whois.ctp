<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div class="whois">
<p class="line">
<?php foreach($whois as $line): ?>
	<?php if (!empty($line) and $line[0] != '%' and $line[0] != '#'): ?>
		<?php
			$elements = explode(':', $line);
			$i = true;
		?>
		<?php foreach($elements as $element): ?>
			<?php if ($i): ?>
				<?php
				echo $element.':<span class="data">';
				$i = false;
				?>
			<?php else: ?>
				<?php echo $element; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		</span><br/>
	<?php endif; ?>
<?php endforeach; ?>
</p>
</div>
